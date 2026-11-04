<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Support\FormFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminFormController extends Controller
{
    public function index()
    {
        $forms = Form::withCount([
            'submissions',
            'submissions as new_count' => fn ($q) => $q->notExported(),
        ])->orderByDesc('is_system')->orderBy('name')->get();

        return view('admin.pages.forms.index', compact('forms'));
    }

    public function create()
    {
        $form = new Form([
            'name' => '',
            'status' => 'open',
            'settings' => Form::defaultSettings(),
            'fields' => FormFields::normalize([
                ['key' => 'name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'maps_to' => 'FULLNAME'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
            ]),
        ]);

        return view('admin.pages.forms.edit', compact('form'));
    }

    public function store(Request $request)
    {
        $form = new Form();
        $this->fill($request, $form);
        $form->save();

        return redirect()->route('admin.forms.edit', $form)->with('success', 'Form created. Copy its link to share it.');
    }

    public function edit(Form $form)
    {
        return view('admin.pages.forms.edit', compact('form'));
    }

    public function update(Request $request, Form $form)
    {
        $this->fill($request, $form);
        $form->save();

        return back()->with('success', 'Form saved.');
    }

    public function duplicate(Form $form)
    {
        $copy = $form->replicate(['is_system']);
        $copy->name = $form->name . ' (copy)';
        $copy->slug = $form->slug . '-copy';
        $copy->is_system = false;
        $copy->save();

        return redirect()->route('admin.forms.edit', $copy)->with('success', 'Form duplicated.');
    }

    public function destroy(Form $form)
    {
        abort_if($form->is_system, 403, 'Built-in forms cannot be deleted.');
        $form->delete();

        return redirect()->route('admin.forms.index')->with('success', 'Form deleted.');
    }

    protected function fill(Request $request, Form $form): void
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:500',
            'fields_json' => 'nullable|string',
            'status' => 'required|in:open,closed',
            'closes_at' => 'nullable|date',
            'submit_label' => 'nullable|string|max:40',
            'success_message' => 'nullable|string|max:300',
            'consent_required' => 'nullable|boolean',
            'consent_text' => 'nullable|string|max:500',
            'notify_email' => 'nullable|email|max:190',
            'auto_reply' => 'nullable|boolean',
            'auto_reply_subject' => 'nullable|string|max:150',
            'auto_reply_body' => 'nullable|string|max:2000',
            'resource_enabled' => 'nullable|boolean',
            'resource_title' => 'nullable|string|max:120',
            'resource_kind' => 'nullable|in:file,link',
            'resource_file' => 'nullable|file|mimes:pdf|max:20480',
            'remove_resource_file' => 'nullable|boolean',
            'resource_url' => 'nullable|url|max:500',
            'resource_email_subject' => 'nullable|string|max:150',
            'resource_email_body' => 'nullable|string|max:2000',
            'popup_title' => 'nullable|string|max:100',
            'popup_message' => 'nullable|string|max:400',
        ]);

        $form->name = $data['name'];
        $form->description = $data['description'] ?? null;
        $form->status = $data['status'];
        $form->closes_at = $data['closes_at'] ?? null;

        if (!$form->is_system) {
            $form->slug = Str::slug($data['slug'] ?? '') ?: ($form->exists ? $form->slug : Str::slug($data['name']));
            $decoded = json_decode((string) ($data['fields_json'] ?? '[]'), true);
            $form->fields = FormFields::normalize(is_array($decoded) ? $decoded : []);
        }

        $old = array_merge(Form::defaultSettings(), $form->settings ?? []);
        $resource = $this->resourceSettings($request, $old);

        $form->settings = array_merge(Form::defaultSettings(), $resource, [
            'submit_label' => $data['submit_label'] ?? 'Send',
            'success_message' => $data['success_message'] ?? Form::defaultSettings()['success_message'],
            'consent_required' => $request->boolean('consent_required'),
            'consent_text' => $data['consent_text'] ?? Form::defaultSettings()['consent_text'],
            'notify_email' => $data['notify_email'] ?? '',
            'auto_reply' => $request->boolean('auto_reply'),
            'auto_reply_subject' => $data['auto_reply_subject'] ?? Form::defaultSettings()['auto_reply_subject'],
            'auto_reply_body' => $data['auto_reply_body'] ?? Form::defaultSettings()['auto_reply_body'],
        ]);
    }

    /** Keep, replace or remove the PDF, and collect the resource settings. Files live on the private disk. */
    protected function resourceSettings(Request $request, array $old): array
    {
        $path = (string) $old['resource_path'];
        $filename = (string) $old['resource_filename'];

        if ($request->boolean('remove_resource_file') && $path !== '') {
            Storage::disk('private')->delete($path);
            $path = $filename = '';
        }
        if ($request->hasFile('resource_file')) {
            if ($path !== '') {
                Storage::disk('private')->delete($path);
            }
            $file = $request->file('resource_file');
            $path = $file->storeAs('form-resources', Str::uuid() . '.pdf', 'private');
            $filename = Str::limit(preg_replace('/[^A-Za-z0-9._ -]/', '', $file->getClientOriginalName()) ?: 'resource.pdf', 120, '');
        }

        $defaults = Form::defaultSettings();

        return [
            'resource_enabled' => $request->boolean('resource_enabled'),
            'resource_title' => $request->input('resource_title', ''),
            'resource_kind' => $request->input('resource_kind', 'file'),
            'resource_path' => $path,
            'resource_filename' => $filename,
            'resource_url' => $request->input('resource_url', ''),
            'resource_email_subject' => $request->input('resource_email_subject') ?: $defaults['resource_email_subject'],
            'resource_email_body' => $request->input('resource_email_body') ?: $defaults['resource_email_body'],
            'popup_title' => $request->input('popup_title') ?: $defaults['popup_title'],
            'popup_message' => $request->input('popup_message') ?: $defaults['popup_message'],
        ];
    }
}
