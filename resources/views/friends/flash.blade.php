@if (session('status') || $errors->any())
    <div class="toast-flash {{ $errors->any() ? 'toast-error' : 'toast-success' }}" id="flashToast">
        <span class="toast-icon">
            @if ($errors->any())
                <i class="bi bi-exclamation-circle-fill"></i>
            @else
                <i class="bi bi-check-circle-fill"></i>
            @endif
        </span>
        <span class="toast-message">{{ $errors->any() ? $errors->first() : session('status') }}</span>
        <button type="button" class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    </div>

    <style>
        .toast-flash {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--fp-bg, #ffffff);
            color: var(--fp-text, #050505);
            border-left: 4px solid var(--toast-accent);
            border-radius: 8px;
            padding: 12px 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
            min-width: 260px;
            max-width: 90vw;
            animation: toast-fade 4s ease forwards;
            pointer-events: auto;
        }

        .toast-success { --toast-accent: #198754; }
        .toast-error   { --toast-accent: #dc3545; }

        .toast-icon {
            font-size: 1.2rem;
            color: var(--toast-accent);
            flex-shrink: 0;
            display: flex;
        }

        .toast-message {
            flex: 1;
            font-size: 0.92rem;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 1.15rem;
            line-height: 1;
            color: var(--fp-text-secondary, #65676b);
            cursor: pointer;
            padding: 0 2px;
        }

        @keyframes toast-fade {
            0%   { opacity: 0; transform: translateX(-50%) translateY(-12px); }
            8%   { opacity: 1; transform: translateX(-50%) translateY(0); }
            88%  { opacity: 1; transform: translateX(-50%) translateY(0); }
            100% { opacity: 0; transform: translateX(-50%) translateY(-12px); }
        }
    </style>

    <script>
        setTimeout(function () {
            var el = document.getElementById('flashToast');
            if (el) el.remove();
        }, 4000);
    </script>
@endif