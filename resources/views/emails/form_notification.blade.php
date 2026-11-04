New response to "{{ $form->name }}"

@foreach ($submission->data as $row)
{{ $row['label'] }}: {{ is_array($row['value']) ? implode(', ', $row['value']) : $row['value'] }}
@endforeach

@if ($submission->source_label)
Came from: {{ $submission->source_label }}
@endif
Received: {{ $submission->created_at->format('j M Y, g:ia') }}

Read it in the admin: {{ route('admin.responses.show', $submission) }}
