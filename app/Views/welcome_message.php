<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>비에비스 나무병원</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Noto Sans KR', sans-serif;
            overflow: hidden;
        }
        .bg-image {
            background-image: url('/public/assets/images/bg-hospital.png');
            height: 100%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            position: relative;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.1); /* Slight overlay for text readability if needed */
        }
        .content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 2;
        }
        .logo-text {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            letter-spacing: 2px;
        }
        .sub-text {
            font-size: 1.5rem;
            margin-bottom: 3rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        .btn-container {
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        .btn-login {
            display: inline-block;
            padding: 15px 40px;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            border: 2px solid transparent;
        }
        .btn-login:hover {
            background-color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.3);
        }
        .btn-login.user {
            background-color: #0056b3;
            color: white;
        }
        .btn-login.user:hover {
            background-color: #004494;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="bg-image">
        <div class="overlay"></div>
        <div class="content">
            <div class="logo-text">VIEVIS NAMUH HOSPITAL</div>
            <div class="sub-text">비에비스 나무병원에 오신 것을 환영합니다</div>
            <div class="btn-container">
                <a href="/user/login" class="btn-login user">사용자 로그인</a>
                <a href="/mngr/login" class="btn-login">관리자 로그인</a>
            </div>
        </div>
    </div>
</body>
</html>
