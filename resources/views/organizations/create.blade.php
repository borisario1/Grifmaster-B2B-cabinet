@extends('layouts.app')

@section('title', 'Добавить организацию')

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> → 
        <a href="{{ route('organizations.index') }}">Мои организации</a> →
        <span>Добавить</span>
    </div>

    <h1 class="page-title">Добавить организацию</h1>
    <p class="page-subtitle">Введите ИНН — основные данные подтянутся автоматически из ФНС.</p>

    {{-- Ошибки валидации (серверные) --}}
    @if($errors->any())
        <div class="form-error">
            @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    {{-- Ошибки поиска (клиентские) --}}
    <div id="js-error" class="form-error" style="display: none;"></div>

    <form method="POST" action="{{ route('organizations.store') }}" class="form-block">
        @csrf
        <input type="hidden" name="dadata_raw" id="dadata_raw">

        {{-- СЕКЦИЯ ПОИСКА --}}
        <div class="form-row">
            <div class="form-group">
                <label>Поиск по ИНН (10 или 12 цифр)</label>
                <div style="gap: 20px;">
                    <input type="text" id="inn_search" class="form-input" placeholder="770505..." autocomplete="off" style="flex-grow: 1;">
                    <button type="button" class="btn-primary btn-big" id="btn_search">Найти</button>
                </div>
            </div>
        </div>

        {{-- РЕЗУЛЬТАТЫ МНОЖЕСТВЕННОГО ПОИСКА --}}
        <div id="search_results" class="card-info" style="display: none; padding: 15px; margin-top: -10px; border-color: #3295D1;">
            {{-- Сюда JS вставит список филиалов --}}
        </div>

        {{-- ПОЛЯ ОРГАНИЗАЦИИ (ЗАПОЛНЯЮТСЯ АВТОМАТОМ) --}}
        <div id="org_fields" style="display:none; margin-top: 10px;">
            
            <div class="form-group">
                <label>Название организации *</label>
                <input type="text" name="name" id="name" class="form-input" required readonly>
            </div>

            <div class="form-two">
                <div class="form-group">
                    <label>ИНН *</label>
                    <input type="text" name="inn" id="inn" class="form-input" required readonly>
                </div>
                <div class="form-group">
                    <label>КПП (только для ООО)</label>
                    <input type="text" name="kpp" id="kpp" class="form-input" readonly>
                </div>
            </div>

            <div class="form-two">
                <div class="form-group">
                    <label>ОГРН / ОГРНИП</label>
                    <input type="text" name="ogrn" id="ogrn" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label>Юридический адрес</label>
                    <input type="text" name="address" id="address" class="form-input" readonly>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn-primary btn-big">
                    <i class="bi bi-check-lg"></i> Сохранить организацию
                </button>
            </div>
        </div>
    </form>

    {{-- Системный отступ перед возвратом --}}
    <div style="margin-top: 35px; border-top: 1px solid #eee; padding-top: 15px;">
        <a href="{{ route('organizations.index') }}" class="btn-link-back">← Отмена и возврат к списку</a>
    </div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("btn_search");
    const inp = document.getElementById("inn_search");
    const block = document.getElementById("org_fields");
    const errorBlock = document.getElementById("js-error");
    const resultsBlock = document.getElementById("search_results");
    
    const searchCache = {};
    let currentSuggestions = [];

    const fields = {
        inn: document.getElementById("inn"),
        name: document.getElementById("name"),
        kpp: document.getElementById("kpp"),
        ogrn: document.getElementById("ogrn"),
        address: document.getElementById("address"),
        raw: document.getElementById("dadata_raw")
    };

    inp.addEventListener("input", function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 12);
    });

    btn.addEventListener("click", findOrg);
    inp.addEventListener("keypress", (e) => { if(e.key === "Enter") { e.preventDefault(); findOrg(); } });

    function showError(msg) {
        errorBlock.textContent = msg;
        errorBlock.style.display = 'block';
        block.style.display = 'none';
        resultsBlock.style.display = 'none';
    }

    async function findOrg() {
        let inn = inp.value.trim();
        errorBlock.style.display = 'none';
        resultsBlock.style.display = 'none';

        if (inn.length !== 10 && inn.length !== 12) {
            showError("ИНН должен содержать 10 (ООО) или 12 (ИП) цифр.");
            return;
        }

        if (searchCache[inn]) {
            handleResponse(searchCache[inn], inn);
            return;
        }

        setLoading(true);

        try {
            let response = await fetch("{{ route('organizations.lookup') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ inn: inn })
            });

            let data = await response.json();
            setLoading(false);

            if (data.ok) searchCache[inn] = data;
            handleResponse(data, inn);

        } catch(e) {
            showError("Сервис временно недоступен. Введите данные вручную.");
            setLoading(false);
            enableManualMode(inn);
        }
    }

    function handleResponse(data, inn) {
        if (!data.ok) {
            showError(data.error || "Ошибка поиска");
            enableManualMode(inn);
            return;
        }

        currentSuggestions = data.suggestions;

        if (data.suggestions.length === 1) {
            fillFields(data.suggestions[0].data, data.suggestions[0], true);
            block.style.display = "block";
        } else {
            renderResultsList(data.suggestions);
        }
    }

    function renderResultsList(suggestions) {
        resultsBlock.innerHTML = '<div style="margin-bottom:10px; font-weight:600; font-size:14px; color:#0B466E;">Найдено несколько подразделений. Выберите нужное:</div>';
        resultsBlock.style.display = 'block';
        
        suggestions.forEach((item, index) => {
            const div = document.createElement('div');
            div.className = "info-row";
            div.style.cursor = "pointer";
            div.style.padding = "10px";
            div.style.borderRadius = "8px";
            
            const kpp = item.data.kpp ? ` (КПП: ${item.data.kpp})` : '';
            div.innerHTML = `<strong>${item.value}</strong> <span>${kpp}<br><small>${item.data.address.value}</small></span>`;
            
            div.onclick = () => {
                fillFields(currentSuggestions[index].data, currentSuggestions[index], true);
                block.style.display = "block";
                resultsBlock.style.display = 'none';
            };
            resultsBlock.appendChild(div);
        });
    }

    function fillFields(d, suggestion, lock = true) {
        fields.inn.value = d.inn || suggestion.data.inn || inp.value;
        fields.name.value = suggestion.value; 
        fields.kpp.value = d.kpp || "";
        fields.ogrn.value = d.ogrn || "";
        fields.address.value = d.address ? d.address.value : "";
        fields.raw.value = JSON.stringify(d);

        if (lock) {
            Object.values(fields).forEach(el => {
                if(el && el.type !== 'hidden') {
                    el.readOnly = true;
                    el.classList.add('form-input-readonly'); // Можно добавить в CSS
                    el.style.background = "#f2f2f2";
                }
            });
        }
    }

    function enableManualMode(innValue) {
        block.style.display = "block";
        fields.inn.value = innValue;
        Object.values(fields).forEach(el => {
            if(el && el.type !== 'hidden') {
                el.readOnly = false;
                el.style.background = "#fff";
                if(el !== fields.inn) el.value = "";
            }
        });
    }

    function setLoading(state) {
        btn.disabled = state;
        btn.innerHTML = state ? '<div class="loader-spinner"></div>' : "Найти";
    }
});
</script>
@endsection