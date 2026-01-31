<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение скачивания</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #3295D1;
            --text-dark: #001F33;
            --min_radius: 16px;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .confirm-container {
            background: white;
            border-radius: var(--min_radius);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 700px;
            width: 100%;
            overflow: hidden;
        }
        
        .confirm-header {
            background: var(--primary-color);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .confirm-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        
        .confirm-body {
            padding: 40px;
        }
        
        .confirm-text {
            font-size: 15px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 30px;
        }
        
        .confirm-text p {
            margin-bottom: 15px;
        }
        
        .confirm-text strong {
            color: var(--text-dark);
            font-weight: 600;
        }
        
        .confirm-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-confirm {
            padding: 14px 32px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--min_radius);
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-confirm:hover {
            background: #2a7db5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(50, 149, 209, 0.4);
            color: white;
        }
        
        .btn-cancel {
            padding: 14px 32px;
            background: #e0e0e0;
            color: #666;
            border: none;
            border-radius: var(--min_radius);
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-cancel:hover {
            background: #d0d0d0;
            color: #333;
        }
        
        .document-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary-color);
        }
        
        .document-info h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0 0 10px 0;
        }
        
        .document-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="confirm-container">
        <div class="confirm-header">
            <h1>Подтверждение скачивания</h1>
        </div>
        
        <div class="confirm-body">
            <div class="document-info">
                <h3>{{ $resource->title }}</h3>
                @if($resource->brand)
                    <p><i class="bi bi-building"></i> Бренд: {{ $resource->brand->name }}</p>
                @endif
                @if(!$resource->external_link)
                    <p><i class="bi bi-file-earmark"></i> Размер: {{ bytesToHuman($resource->getFileSize()) }}</p>
                @endif
            </div>
            
            <div class="confirm-text">
                {!! $resource->confirmation_text !!}
            </div>
            
            <div class="confirm-actions">
                <a href="{{ route('files.index') }}" class="btn-cancel">
                    <i class="bi bi-x-circle"></i> Отмена
                </a>
                <a href="{{ route('files.download', $resource->id) }}?confirmed=1&token={{ $token }}" class="btn-confirm">
                    <i class="bi bi-download"></i> {{ $resource->confirm_btn_text }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>
