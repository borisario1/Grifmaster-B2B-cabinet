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
    
    // КЭШ: защищаем кошелек и сервер от повторных кликов
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

    // Маска: только цифры, макс 12
    inp.addEventListener("input", function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 12);
    });

    btn.addEventListener("click", findOrg);
    inp.addEventListener("keypress", (e) => { 
        if(e.key === "Enter") { 
            e.preventDefault(); 
            findOrg(); 
        } 
    });

    function showError(msg) {
        errorBlock.textContent = msg;
        errorBlock.style.display = 'block';
        block.style.display = 'none';
        resultsBlock.style.display = 'none';
    }

    function clearError() {
        errorBlock.textContent = '';
        errorBlock.style.display = 'none';
    }

    async function findOrg() {
        let inn = inp.value.trim();
        clearError();
        resultsBlock.style.display = 'none';

        if (inn.length !== 10 && inn.length !== 12) {
            showError("ИНН должен содержать 10 (ООО) или 12 (ИП) цифр.");
            return;
        }

        // Если уже искали этот ИНН - берем из памяти мгновенно
        if (searchCache[inn]) {
            console.log("Взято из кэша");
            handleResponse(searchCache[inn], inn);
            return;
        }

        setLoading(true);

        try {
            let response = await fetch("{{ route('organizations.lookup') }}", {
                method: "POST",
                headers: { 
                    "Content-Type": "application/json", 
                    "X-CSRF-TOKEN": "{{ csrf_token() }}" 
                },
                body: JSON.stringify({ inn: inn })
            });

            let data = await response.json();
            setLoading(false);

            if (data.ok) {
                searchCache[inn] = data; // Запоминаем результат
            }
            
            handleResponse(data, inn);

        } catch(e) {
            console.error(e);
            showError("Ошибка соединения. Попробуйте заполнить вручную.");
            setLoading(false);
            enableManualMode(inn);
        }
    }

    function handleResponse(data, inn) {
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

        // Сохраняем предложения глобально для функции клика по списку
        currentSuggestions = data.suggestions;

        if (data.suggestions.length === 1) {
            fillFields(data.suggestions[0].data, data.suggestions[0], true);
            block.style.display = "block";
        } else {
            renderResultsList(data.suggestions);
        }
    }

    function renderResultsList(suggestions) {
        resultsBlock.innerHTML = '';
        resultsBlock.style.display = 'block';
        
        const header = document.createElement('div');
        header.className = 'list-group-item list-group-item-light fw-bold';
        header.textContent = 'Найдено несколько филиалов. Выберите нужный:';
        header.style.padding = '10px 15px';
        resultsBlock.appendChild(header);

        suggestions.forEach((item, index) => {
            const btnItem = document.createElement('button');
            btnItem.type = 'button';
            btnItem.className = "list-group-item list-group-item-action";
            btnItem.style.textAlign = 'left';
            
            const kpp = item.data.kpp ? ` (КПП: ${item.data.kpp})` : '';
            btnItem.innerHTML = `<strong>${item.value}</strong>${kpp}<br><small>${item.data.address.value}</small>`;
            
            btnItem.onclick = (e) => {
                e.preventDefault();
                fillFields(currentSuggestions[index].data, currentSuggestions[index], true);
                block.style.display = "block";
                resultsBlock.style.display = 'none';
            };
            resultsBlock.appendChild(btnItem);
        });
    }

    function fillFields(d, suggestion, lock = true) {
        clearError(); 
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
                    el.style.backgroundColor = "#f9f9f9";
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
                el.style.backgroundColor = "";
                el.value = (el === fields.inn) ? innValue : "";
            }
        });
    }

    function setLoading(state) {
            btn.disabled = state;
            if (state) {
                // Устанавливаем фиксированную ширину перед сменой текста, чтобы кнопка не дергалась
                btn.style.width = btn.offsetWidth + 'px';
                btn.innerHTML = '<div class="loader-spinner"></div> ...';
            } else {
                btn.style.width = ''; // Возвращаем автоматическую ширину
                btn.innerHTML = "Найти";
            }
        }
});
</script>
@endsection