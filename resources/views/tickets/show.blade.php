@extends('layouts.app')

@section('title', $ticket->topic)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/requests.css') }}">
    <style>
        .file-upload-zone {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.2s;
        }
        .file-upload-zone:hover { border-color: #007bff; background: #f0f7ff; }
        .file-upload-zone.dragover { border-color: #007bff; background: #e8f4ff; }
        .file-upload-zone input[type="file"] { display: none; }
        .file-list { margin-top: 10px; }
        .file-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-bottom: 5px;
        }
        .file-item .file-size { color: #888; font-size: 12px; }
        .file-item .remove-file { cursor: pointer; color: #dc3545; margin-left: auto; }
        .msg-attachments { margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(0,0,0,0.1); }
        .msg-attachment {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(255,255,255,0.8);
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            color: #333;
            margin: 3px;
            transition: background 0.2s;
        }
        .msg-attachment:hover { background: rgba(255,255,255,1); }
        .msg-attachment i { font-size: 16px; color: #6b7280; }
        .msg-attachment .att-size { color: #888; font-size: 11px; }
        .upload-hint { color: #666; font-size: 13px; }
    </style>
@endpush

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> →
        <a href="{{ route('tickets.index') }}">Обращения</a> →
        <span>{{ $ticket->topic }}</span>
    </div>

    <h1 class="page-title">{{ $ticket->topic }}</h1>
    <p class="page-subtitle">
        № {{ $ticket->request_code }} ·
        Статус: <strong>{{ $ticket->status_label }}</strong>
    </p>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <!-- INFO карточка -->
    <div class="card-info">
        <div class="info-row">
            <span>Номер обращения</span>
            <strong>{{ $ticket->request_code }}</strong>
        </div>
        <div class="info-row">
            <span>Категория</span>
            <strong>{{ $ticket->category_label }}</strong>
        </div>
        <div class="info-row">
            <span>Создано</span>
            <strong>{{ $ticket->created_at->format('d.m.Y H:i') }}</strong>
        </div>
        @if($ticket->org_name)
        <div class="info-row">
            <span>Организация</span>
            <strong>{{ $ticket->org_name }}</strong>
        </div>
        @endif
    </div>

    <!-- Чат -->
    <div class="chat-box" id="chatBox">
        @foreach($ticket->messages as $msg)
            @if($msg->sender_type === 'system')
                <div class="msg msg-system">
                    {!! nl2br(e($msg->message)) !!}
                    <div class="msg-time">{{ $msg->created_at->format('d.m.Y H:i') }}</div>
                </div>
            @else
                <div class="msg {{ $msg->sender_type === 'user' ? 'msg-user' : 'msg-staff' }}">
                    <div class="msg-text">{!! nl2br(e($msg->message)) !!}</div>
                    
                    @if($msg->attachments && $msg->attachments->count() > 0)
                        <div class="msg-attachments">
                            @foreach($msg->attachments as $att)
                                <a href="{{ route('tickets.attachment', $att->id) }}" class="msg-attachment" target="_blank">
                                    <i class="bi {{ $att->icon }}"></i>
                                    <span>{{ Str::limit($att->original_name, 25) }}</span>
                                    <span class="att-size">({{ $att->human_size }})</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="msg-time">{{ $msg->created_at->format('d.m.Y H:i') }}</div>
                </div>
            @endif
        @endforeach
    </div>

    @if($ticket->status !== 'closed')
        <form method="POST" action="{{ route('tickets.message', $ticket->request_code) }}" class="chat-send" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <textarea name="message" class="form-input" rows="3" placeholder="Введите сообщение..." required></textarea>
            </div>
            
            <!-- Загрузка файлов -->
            <div class="file-upload-zone" id="uploadZone">
                <input type="file" name="attachments[]" id="fileInput" multiple accept="image/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                <div class="upload-hint">
                    <i class="bi bi-paperclip"></i> Прикрепить файлы (до 5 файлов, макс. 100 МБ суммарно)
                </div>
            </div>
            <div class="file-list" id="fileList"></div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send-fill"></i> Отправить
                </button>

                <a href="#" 
                   class="btn btn-secondary" 
                   onclick="event.preventDefault(); openModal(
                       'universalConfirm',
                       () => window.location.href = '{{ route('tickets.close', $ticket->request_code) }}',
                       'Закрытие обращения',
                       'Вы действительно хотите закрыть это обращение? Если передумаете, вам придется создать новое.',
                       5,
                       'Закрыть'
                   )">
                    Закрыть обращение
                </a>
            </div>
        </form>
    @else
        <div class="alert alert-info mt-20">
            <i class="bi bi-check-circle"></i>
            Это обращение закрыто. Если у вас возник новый вопрос, пожалуйста, создайте новое обращение.
        </div>
    @endif

    <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
        <a href="{{ route('tickets.index') }}" class="btn-link-back">← К списку обращений</a>
    </div>

    <script>
        // Scroll chat to bottom
        const box = document.getElementById('chatBox');
        if (box) box.scrollTop = box.scrollHeight;

        // File upload handling
        const zone = document.getElementById('uploadZone');
        const input = document.getElementById('fileInput');
        const list = document.getElementById('fileList');
        const maxFiles = 5;
        const maxTotalSize = 100 * 1024 * 1024; // 100MB

        zone.addEventListener('click', () => input.click());
        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('dragover');
            input.files = e.dataTransfer.files;
            updateFileList();
        });

        input.addEventListener('change', updateFileList);

        function updateFileList() {
            list.innerHTML = '';
            let totalSize = 0;
            const files = Array.from(input.files).slice(0, maxFiles);
            
            files.forEach((file, index) => {
                totalSize += file.size;
                const item = document.createElement('div');
                item.className = 'file-item';
                item.innerHTML = `
                    <i class="bi bi-file-earmark"></i>
                    <span>${file.name}</span>
                    <span class="file-size">${formatBytes(file.size)}</span>
                `;
                list.appendChild(item);
            });

            if (totalSize > maxTotalSize) {
                list.innerHTML += '<div class="alert alert-warning" style="margin-top: 10px;">Суммарный размер превышает 100 МБ</div>';
            }
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Б';
            const k = 1024;
            const sizes = ['Б', 'КБ', 'МБ', 'ГБ'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
@endsection
