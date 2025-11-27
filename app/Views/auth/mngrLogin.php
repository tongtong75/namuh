<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIEVIS NAMUH 로그인</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            /* '/images/bg_namuh01.png' 경로가 웹 루트 기준인지 확인 필요 */
            /* CodeIgniter에서는 base_url() 헬퍼 사용 권장 */
            background: url('<?= base_url('assets/images/bg_namuh01.png') ?>') no-repeat center center fixed;
            background-size: cover;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .login-container h1 { color: #333; margin-bottom: 30px; }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: calc(100% - 20px); padding: 10px; margin-bottom: 15px;
            border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;
        }
        .login-container button {
            width: 100%; padding: 10px; background-color: #4285f4;
            color: white; border: none; border-radius: 5px;
            cursor: pointer; transition: background-color 0.3s ease;
        }
        .login-container button:hover { background-color: #3367d6; }
        .login-container h2 { margin-bottom: 25px; }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        @media (max-width: 768px) {
            .login-container { width: 90%; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>VIEVIS NAMUH</h1>
        <h2>관리자 로그인</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert" role="alert">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success" role="alert">
                <?= session()->getFlashdata('message') ?>
            </div>
        <?php endif; ?>
        
        <?= form_open(site_url('auth/attemptLogin')) ?>
            <?= csrf_field() ?>
            <input type="text" name="mngr_id" placeholder="아이디를 입력해 주세요" value="<?= old('mngr_id') ?>" required>
            <input type="password" name="password" placeholder="비밀번호를 입력해 주세요" required>
            <button type="submit">로그인</button>
        <?= form_close() ?>
    </div>
</body>
</html>