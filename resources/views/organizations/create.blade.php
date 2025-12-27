@extends('layouts.app')

@section('title', 'Добавить организацию')

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('organizations.index') }}">Мои организации</a> →
        <span>Добавить</span>
    </div>

    <h1 class="page-title">Добавить организацию</h1>
    <p class="page-subtitle">Введите ИНН — остальные данные подставятся автоматически.</p>

    {{-- Блок для ошибок сервера --}}
    @if($errors->any())
        <div class="form-error">
            @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    {{-- Блок для JS ошибок --}}
    <div id="js-error" class="form-error" style="display: none;"></div>

    <form method="POST" action="{{ route('organizations.store') }}" class="form-block">
        @csrf
        <input type="hidden" name="dadata_raw" id="dadata_raw">

        <div class="form-row search-row" style="position: relative;">
            <div class="form-group" style="flex-grow: 1;">
                <label>Поиск по ИНН</label>
                <input type="text" id="inn_search" class="form-input" placeholder="Введите ИНН" autocomplete="off">
            </div>
            <div class="form-group" style="width: auto; align-self: flex-end;">
                <button type="button" class="btn-primary" id="btn_search" style="height: 42px; margin-top: 23px;">Найти</button>
            </div>
        </div>

        <div id="search_results" class="list-group mt-3 mb-4" style="display: none; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            </div>

        <div id="org_fields" style="display:none; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
            
            <div class="form-group">
                <label>ИНН *</label>
                <input type="text" name="inn" id="inn" class="form-input" required readonly style="background-color: #f9f9f9;">
            </div>

            <div class="form-group">
                <label>Название организации *</label>
                <input type="text" name="name" id="name" class="form-input" required readonly style="background-color: #f9f9f9;">
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label>КПП</label>
                    <input type="text" name="kpp" id="kpp" class="form-input" readonly style="background-color: #f9f9f9;">
                </div>
                <div class="form-group half">
                    <label>ОГРН / ОГРНИП</label>
                    <input type="text" name="ogrn" id="ogrn" class="form-input" readonly style="background-color: #f9f9f9;">
                </div>
            </div>

            <div class="form-group">
                <label>Юридический адрес</label>
                <input type="text" name="address" id="address" class="form-input" readonly style="background-color: #f9f9f9;">
            </div>

            <button type="submit" class="btn-primary btn-lg mt-3">
                <i class="bi bi-check-lg"></i> Сохранить
            </button>
        </div>
    </form>

    <br>
    <a href="{{ route('organizations.index') }}" class="btn-link-back">← Отмена</a>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("btn_search");
    const inp = document.getElementById("inn_search");
    const block = document.getElementById("org_fields");
    const errorBlock = document.getElementById("js-error");
    const resultsBlock = document.getElementById("search_results");
    
    const fields = {
        inn: document.getElementById("inn"),
        name: document.getElementById("name"),
        kpp: document.getElementById("kpp"),
        ogrn: document.getElementById("ogrn"),
        address: document.getElementById("address"),
        raw: document.getElementById("dadata_raw")
    };

    let currentSuggestions = [];

    btn.addEventListener("click", findOrg);
    inp.addEventListener("keypress", (e) => { if(e.key === "Enter") { e.preventDefault(); findOrg(); } });

    function showError(msg) {
        errorBlock.textContent = msg;
        errorBlock.style.display = 'block';
        block.style.display = 'none';
        resultsBlock.style.display = 'none'; // Скрываем результаты при ошибке
    }

    function clearError() {
        errorBlock.textContent = '';
        errorBlock.style.display = 'none';
    }

    async function findOrg() {
        let inn = inp.value.trim();
        clearError();
        resultsBlock.style.display = 'none'; // Очищаем список перед новым поиском

        if(!inn) { 
            showError("Введите ИНН"); 
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

            if (!data.ok) {
                showError(data.error || "Ошибка сервера");
                enableManualMode(inn);
                return;
            }

            if (!data.suggestions || !data.suggestions.length) {
                showError("Организации с таким ИНН не найдены.");
                enableManualMode(inn);
                return;
            }

            // --- ВЫБОР ---
            if (data.suggestions.length === 1) {
                // Один вариант - сразу заполняем
                fillFields(data.suggestions[0].data, data.suggestions[0], true);
                block.style.display = "block";
            } else {
                // Много вариантов - ПОКАЗЫВАЕМ СПИСОК
                currentSuggestions = data.suggestions; 
                renderResultsList(data.suggestions);
            }

        } catch(e) {
            console.error(e);
            showError("Ошибка соединения. Попробуйте заполнить вручную.");
            setLoading(false);
            enableManualMode(inn);
        }
    }

    // Рендеринг списка (вместо модалки)
    function renderResultsList(suggestions) {
        resultsBlock.innerHTML = '';
        
        // Заголовок списка
        const header = document.createElement('div');
        header.className = 'list-group-item list-group-item-light fw-bold';
        header.textContent = 'Выберите организацию:';
        header.style.background = '#f8f9fa';
        header.style.padding = '10px 15px';
        header.style.borderBottom = '1px solid #ddd';
        resultsBlock.appendChild(header);

        suggestions.forEach((item, index) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = "list-group-item list-group-item-action";
            btn.style.width = '100%';
            btn.style.textAlign = 'left';
            btn.style.padding = '10px 15px';
            btn.style.border = 'none';
            btn.style.borderBottom = '1px solid #eee';
            btn.style.background = '#fff';
            btn.style.cursor = 'pointer';

            // Наведение мыши
            btn.onmouseover = () => btn.style.background = '#f0f0f0';
            btn.onmouseout = () => btn.style.background = '#fff';
            
            const kppInfo = item.data.kpp ? ` (КПП: ${item.data.kpp})` : '';
            const statusInfo = item.data.state && item.data.state.status !== 'ACTIVE' ? ' <span style="color:red">[НЕ АКТИВНА]</span>' : '';
            const addrInfo = item.data.address && item.data.address.value ? `<div style='font-size:0.85em; color:#666; margin-top:2px;'>${item.data.address.value}</div>` : '';
            
            btn.innerHTML = `<strong>${item.value}</strong>${kppInfo}${statusInfo}${addrInfo}`;
            
            btn.onclick = (e) => {
                e.preventDefault();
                fillFields(currentSuggestions[index].data, currentSuggestions[index], true);
                block.style.display = "block";
                resultsBlock.style.display = 'none'; // Скрываем список после выбора
            };
            resultsBlock.appendChild(btn);
        });

        resultsBlock.style.display = 'block';
    }

    function enableManualMode(innValue) {
        block.style.display = "block";
        fields.inn.value = innValue;
        fields.name.value = "";
        fields.kpp.value = "";
        fields.ogrn.value = "";
        fields.address.value = "";
        fields.raw.value = "";

        Object.values(fields).forEach(el => {
            if(el && el.type !== 'hidden') {
                el.readOnly = false;
                el.style.backgroundColor = "";
            }
        });
    }

    function fillFields(d, suggestion, lock = true) {
        clearError(); 

        fields.inn.value = d.inn || suggestion.data.inn || inp.value;
        fields.name.value = suggestion.value; 
        fields.kpp.value = d.kpp || "";
        fields.ogrn.value = d.ogrn || "";
        
        let addr = "";
        if (d.address && d.address.value) addr = d.address.value;
        else if (d.address && d.address.data && d.address.data.source) addr = d.address.data.source;
        fields.address.value = addr;
        
        fields.raw.value = JSON.stringify(d);

        if (lock) {
            Object.values(fields).forEach(el => {
                if(el && el.type !== 'hidden') {
                    el.readOnly = true;
                    el.style.backgroundColor = "#f9f9f9";
                }
            });
        }
    }

    function setLoading(state) {
        btn.disabled = state;
        btn.innerText = state ? "..." : "Найти";
    }
});
</script>
@endsection