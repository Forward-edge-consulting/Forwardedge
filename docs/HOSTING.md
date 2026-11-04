# Keeping PHP process use bounded

Application settings cannot identify or clear an exhausted hosting account by themselves. First obtain the exact hosting error, the process list, and the existing cron/worker configuration. PHP-FPM or hosting account process limits are controlled on the server, not by `PHP_CLI_SERVER_WORKERS` in Laravel.

## Changes included in this project

- Removed the legacy scheduled failed-mail retry task and its looping command. Failed jobs are no longer automatically resurrected.
- `composer dev` launches only the local web server and Vite. It does not launch queue listeners or log-tail processes.
- Local/example `PHP_CLI_SERVER_WORKERS` is 1. Restart an existing `artisan serve` process for a changed setting to take effect. This setting does not change PHP-FPM or Apache process counts.
- SMTP and Mailchimp calls have 10-second timeouts. Queue jobs have a 25-second timeout and a 60-second retry backoff.
- The hero slideshow serves static images and JavaScript. It never polls PHP for slides.

## Shared hosting: optional bounded queue processing

If the application needs queued work and the host supports cron, use one Laravel scheduler cron entry:

```cron
* * * * * cd /absolute/path/to/Forward-edge && /absolute/path/to/php artisan schedule:run >> /absolute/path/to/scheduler.log 2>&1
```

Set `QUEUE_CRON_ENABLED=true` only if this is the sole queue runner. The scheduler starts one worker with:

```sh
php artisan queue:work --stop-when-empty --max-time=45 --max-jobs=10 --timeout=30 --tries=3 --memory=128
```

It stops when empty, has a maximum job/time budget, and uses a scheduler lock to prevent overlap. A running job finishes or reaches its job timeout before the worker exits; `--max-time` is not an instantaneous process-kill deadline. The queue connection's `retry_after` must remain greater than the worker timeout (the current database/Redis defaults are 90 seconds). Use a persistent cache store for scheduler locks, not the `array` store.

If Supervisor already runs one queue worker, leave `QUEUE_CRON_ENABLED=false`. Do not also launch workers from cron. Avoid `queue:listen`, `schedule:work`, `composer dev`, or repeated background worker launches on shared hosting.

## What to inspect on the server

Use the hosting panel's process/resource view, or these read-only SSH commands:

```sh
ps -u "$USER" -o pid,ppid,etime,args
crontab -l
php artisan schedule:list
php artisan queue:failed
```

Look for duplicate queue workers, old development servers, long-running requests, repeated cron launches, and memory/process-limit faults in the hosting logs. Do not kill every PHP process: identify the unwanted PID and its process manager first. If the panel and SSH are inaccessible, ask hosting support to provide the PHP/entry-process limit and process list and restore access.

After deployment, update production environment values and rebuild config/route/view caches using the host's normal deployment procedure. Do not copy local credentials or the local `.env` to production. No server configuration or remote process has been changed by these repository edits.

## DreamHost

Confirm whether the account is Shared Hosting or Managed VPS. DreamHost monitors combined memory use across a shared user's processes, and does not permit persistent processes on Shared Hosting. A permanently running `queue:work`, `queue:listen`, or `schedule:work` process is inappropriate on a shared plan.

Since SSH is timing out, ask support to restore access and capture the process command lines, start times, and memory usage **before** clearing them. Ask which website and hosting user own them, whether they are PHP FastCGI web workers or Artisan CLI workers, and whether cron is relaunching them. This distinguishes traffic/slow requests from duplicate background processes. Check the account's DreamHost cron settings for duplicate entries as soon as panel access is available.

Use one normal scheduler cron entry only if background jobs are required. Leave `QUEUE_CRON_ENABLED=false` if no worker is needed, and never run the development command on the host. On shared hosting, run cron with a persistent file/database cache so its overlap lock works. The application cannot free a locked-out remote account until support restores access or server credentials become available.

Do not raise PHP's memory limit as a first response: it can increase per-process memory consumption while the hosting limit applies to all processes together.

References:
- https://help.dreamhost.com/hc/en-us/articles/216540488-Why-were-my-processes-killed
- https://help.dreamhost.com/hc/en-us/articles/214869648-Persistent-processes
