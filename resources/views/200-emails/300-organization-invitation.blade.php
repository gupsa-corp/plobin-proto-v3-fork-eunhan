<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $organization->name }} 조직 초대</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #e3e3e3;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .content {
            padding: 30px 0;
        }
        .organization-info {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .organization-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .cta-button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .cta-button:hover {
            background-color: #1d4ed8;
        }
        .footer {
            border-top: 2px solid #e3e3e3;
            padding: 20px 0;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .invitation-details {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .role-info {
            font-weight: bold;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Plobin</div>
        </div>

        <div class="content">
            <h2>조직 초대를 받았습니다!</h2>

            <p>안녕하세요!</p>

            <p>{{ $organization->name }} 조직에 멤버로 초대되었습니다.</p>

            <div class="organization-info">
                <div class="organization-name">{{ $organization->name }}</div>
                <p>이 조직에 참여하여 다양한 프로젝트와 협업 기능을 이용하실 수 있습니다.</p>
            </div>

            <div class="invitation-details">
                <p><strong>초대 정보:</strong></p>
                <ul>
                    <li><strong>조직명:</strong> {{ $organization->name }}</li>
                    <li><strong>역할:</strong> <span class="role-info">{{ $organizationMember->role_name }}</span></li>
                    <li><strong>초대일:</strong> {{ $organizationMember->invited_at->format('Y년 m월 d일') }}</li>
                </ul>
            </div>

            <p>아래 버튼을 클릭하여 초대를 수락하고 조직에 참여해 주세요:</p>

            <div style="text-align: center;">
                <a href="{{ $invitationUrl }}" class="cta-button">
                    초대 수락하기
                </a>
            </div>

            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e3e3e3; color: #6b7280; font-size: 14px;">
                <strong>버튼이 작동하지 않는 경우 아래 링크를 복사하여 브라우저에서 직접 접속해 주세요:</strong><br>
                <a href="{{ $invitationUrl }}" style="color: #2563eb; word-break: break-all;">{{ $invitationUrl }}</a>
            </p>

            <p style="color: #6b7280; font-size: 14px;">
                이 초대는 7일 후 만료됩니다. 초대를 받지 않으셨다면 이 이메일을 무시해 주세요.
            </p>
        </div>

        <div class="footer">
            <p>© 2024 Plobin. All rights reserved.</p>
            <p>문의사항이 있으시면 <a href="mailto:support@plobin.com" style="color: #2563eb;">support@plobin.com</a>으로 연락해 주세요.</p>
        </div>
    </div>
</body>
</html>