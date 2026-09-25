<div id="model-test-result" class="hidden rounded-lg border px-4 py-3 text-sm"></div>

@once
@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function showTestResult(ok, message, details) {
        const el = document.getElementById('model-test-result');
        if (!el) return;
        el.classList.remove('hidden', 'border-[#10b981]/30', 'bg-[#10b981]/5', 'text-[#10b981]', 'border-[#ef4444]/30', 'bg-[#ef4444]/5', 'text-[#ef4444]');
        if (ok) {
            el.classList.add('border-[#10b981]/30', 'bg-[#10b981]/5', 'text-[#10b981]');
        } else {
            el.classList.add('border-[#ef4444]/30', 'bg-[#ef4444]/5', 'text-[#ef4444]');
        }
        el.innerHTML = message + (details ? '<pre class="mt-2 max-h-32 overflow-y-auto whitespace-pre-wrap rounded bg-white/60 p-2 text-xs text-[#1e293b] font-mono">' + details + '</pre>' : '');
    }

    window.runModelSavedTest = async function (url) {
        showTestResult(true, 'Menguji koneksi…');
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({}),
            });
            const data = await res.json();
            if (data.ok) {
                const meta = `Latency: ${data.latency_seconds}s · Tokens: ${(data.prompt_tokens || 0) + (data.completion_tokens || 0)}`;
                showTestResult(true, 'Berhasil. ' + meta, data.output || '(kosong)');
            } else {
                showTestResult(false, data.message || 'Tes gagal.');
            }
        } catch (e) {
            showTestResult(false, 'Tes gagal: ' + e.message);
        }
    };

    window.runModelFormTest = async function (url, options) {
        const provider = document.getElementById(options.providerId || 'provider')?.value;
        const modelName = document.getElementById(options.modelNameId || 'model_name')?.value;
        const apiKey = document.getElementById(options.apiKeyId || 'api_key')?.value;
        const baseUrl = document.getElementById(options.baseUrlId || 'base_url')?.value;

        if (!apiKey && !options.savedModelUrl) {
            showTestResult(false, 'Isi API key terlebih dahulu.');
            return;
        }

        showTestResult(true, 'Menguji koneksi…');

        const payload = {
            base_url: baseUrl || null,
            api_key: apiKey || undefined,
        };

        const targetUrl = options.savedModelUrl || url;
        const body = options.savedModelUrl
            ? JSON.stringify(payload)
            : JSON.stringify({
                provider,
                model_name: modelName,
                api_key: apiKey,
                base_url: baseUrl || null,
            });

        try {
            const res = await fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body,
            });
            const data = await res.json();
            if (data.ok) {
                const meta = `Latency: ${data.latency_seconds}s · Tokens: ${(data.prompt_tokens || 0) + (data.completion_tokens || 0)}`;
                showTestResult(true, 'Berhasil. ' + meta, data.output || '(kosong)');
            } else {
                showTestResult(false, data.message || 'Tes gagal.');
            }
        } catch (e) {
            showTestResult(false, 'Tes gagal: ' + e.message);
        }
    };
})();
</script>
@endpush
@endonce
