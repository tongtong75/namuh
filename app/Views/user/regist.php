<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8" />
    <title>회원가입 | 비에비스 나무병원</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="진료당일 위·대장내시경, 소화기질환, 단일통로 복강경 수술센터, 유방·갑상선센터, 건강검진센터" name="description" />
    <meta content="vievis namuh" name="author" />
    <link rel="shortcut icon" href="<?= base_url('public/assets/images/favicon.png') ?>">
    <?= $this->include('partials/head-css') ?>
    <style>
        .auth-page-wrapper {
            min-height: 100vh;
            padding-bottom: 80px; /* footer 공간 확보 */
        }
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }
        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 1rem 1.5rem;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            background-color: transparent;
        }
        .agreement-box {
            border: 1px solid #dee2e6;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
            width: 150px;
        }
        .info-table td {
            padding: 12px;
            border: 1px solid #dee2e6;
        }
        .family-table {
            width: 100%;
            text-align: center;
        }
        .family-table th {
            background-color: #e9ecef;
            padding: 10px;
            border: 1px solid #dee2e6;
        }
        .family-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
        }
        .family-table-wrapper {
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .complete-message {
            text-align: center;
            padding: 100px 20px;
        }
        .complete-message i {
            font-size: 48px;
            color: #0d6efd;
            margin-bottom: 20px;
        }
        .complete-message h3 {
            color: #0d6efd;
            font-size: 24px;
        }

    </style>
</head>
<body>
    <!-- auth-page wrapper -->
    <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <!-- auth-page content -->
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card overflow-hidden card-bg-fill galaxy-border-none">
                            <div class="card-body p-4">
                                <!-- Tabs -->
                                <ul class="nav nav-tabs mb-4" id="registTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="agreement-tab" data-bs-toggle="tab" data-bs-target="#agreement" type="button" role="tab">약관 동의</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" disabled>비밀번호 변경</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="complete-tab" data-bs-toggle="tab" data-bs-target="#complete" type="button" role="tab" disabled>가입완료</button>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content" id="registTabContent">
                                    <!-- 약관 동의 Tab -->
                                    <div class="tab-pane fade show active" id="agreement" role="tabpanel">
                                        <h5 class="mb-3"><i class="ri-file-list-3-line"></i> 검진시스템 이용약관 동의</h5>
                                        
                                        <h6 class="mb-2"><i class="ri-file-list-3-line"></i> 검진시스템 이용약관</h6>
                                        <div class="agreement-box">
                                            <p>웹활용 입직원 건강검진을 위하여 귀하의 개인정보를 아래와 같이 수집·활용·제공하고자 합니다. 아래의 사항에 대해 충분히 읽어보신 후 동의 여부를 체크, 서명하여 주시기 바랍니다. 또한 정보주체자의 개인정보를 중요시하며, 「개인정보보호법」, 「정보통신망 이용촉진 및 정보보호 등에 관한 법률」 등 관련 법령상의 안전조치규정을 준수하고, 관련 법령에 의거한 개인정보 취급 지침을 정하여 직원 권익 보호에 최선을 다하고 있습니다.</p>
                                            
                                            <p>1. 수집 목적 가) 건강검진 이용안내서비스 제공 및 사후 관리, 진료의뢰 등 서비스 제공(문자/음성, 이메일발송 등) 나) 검진 여부 및 의뢰된, 건강검진 기본결과 관리팀에 의거한 정보 제공 다) 직장검진의 경우 회사의 요청시 회사와 협약한 의료기관에 따라 사업장의 요청 시 관련 정보를 제공할 수 있습니다.</p>
                                            
                                            <p>2. 정보 수집 범위 성명, 생년월일, 주소, 일반전화, 휴대전화, 이메일, 회사명, 검진결과 등</p>
                                            
                                            <p>3. 개인정보의 보유 및 이용기간 개인정보의 보유 및 이용기간은 의료법을 관계법령의 준하며 다, 끝으시 기간이 연장될 수 있습니다.</p>
                                        </div>

                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="agreeCheck">
                                            <label class="form-check-label" for="agreeCheck">
                                                검진시스템 이용약관에 동의합니다.
                                            </label>
                                        </div>

                                        <div class="text-center">
                                            <button type="button" class="btn btn-primary me-2" id="btnAgree">동의함</button>
                                            <button type="button" class="btn btn-secondary" id="btnDisagree">동의안함</button>
                                        </div>
                                    </div>

                                    <!-- 비밀번호 변경 Tab -->
                                    <div class="tab-pane fade" id="password" role="tabpanel">
                                        <h5 class="mb-3"><i class="ri-lock-password-line"></i> 회원정보입력</h5>
                                        
                                        <div class="alert alert-info">
                                            <ul class="mb-0">
                                                <li>회원정보는 개인정보보호법에 따라 안전하게 보호되며, 특히 개인식별정보는 암호화되어 DB에 저장됩니다.</li>
                                                <li>회원정보 중 사진의 회원의 계약에 의해 제공받은 정보는 수정하실 수 없으므로, 변경사항이 있으면 회사담당자에게 문의하시기 바랍니다.</li>
                                            </ul>
                                        </div>

                                        <h6 class="mb-2"><i class="ri-user-line"></i> 회사로부터 제공받은 기본정보(기본정보가 잘못 입력되어 있을 경우 회사 담당자에게 문의하시기 바랍니다.)</h6>
                                        
                                        <table class="info-table">
                                            <tr>
                                                <th>회사명</th>
                                                <td><?= session()->get('co_nm') ?? 'N/A' ?></td>
                                                <th>사번</th>
                                                <td><?= session()->get('user_id') ?? 'N/A' ?></td>
                                            </tr>
                                            <tr>
                                                <th>직원이름</th>
                                                <td><?= session()->get('user_name') ?? 'N/A' ?></td>
                                                <th>비고</th>
                                                <td></td>
                                            </tr>
                                        </table>

                                        <h6 class="mb-2">가족</h6>
                                        <div class="family-table-wrapper">
                                            <table class="family-table mb-4">
                                                <thead>
                                                    <tr>
                                                        <th>관계</th>
                                                        <th>이름</th>
                                                        <th>생년월일</th>
                                                        <th>비고</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $relationMap = [
                                                        'W' => '배우자',
                                                        'C' => '자녀',
                                                        'O' => '기타',
                                                        'P' => '부모님'
                                                    ];
                                                    ?>
                                                    <?php if (isset($familyMembers) && !empty($familyMembers)): ?>
                                                        <?php foreach ($familyMembers as $member): ?>
                                                            <tr>
                                                                <td><?= $relationMap[$member['RELATION']] ?? $member['RELATION'] ?></td>
                                                                <td><?= $member['NAME'] ?></td>
                                                                <td><?= $member['BIRTHDAY'] ?></td>
                                                                <td></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4">등록된 가족 정보가 없습니다.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <h6 class="mb-2"><i class="ri-key-line"></i> 아이디 / 비밀번호 등록(아이디는 회사의의 정책에 따라 고정되어있 않습니다.)</h6>
                                        
                                        <table class="info-table">
                                            <tr>
                                                <th>아이디</th>
                                                <td><?= session()->get('user_id') ?? 'N/A' ?></td>
                                            </tr>
                                            <tr>
                                                <th>비밀번호</th>
                                                <td>
                                                    <input type="password" class="form-control" id="newPassword" placeholder="비밀번호를 입력해주세요">
                                                    <small class="text-danger">* 8~12자리로 구성하되, 영문, 숫자, 특수문자 조합하여 구성하셔야 합니다.</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>비밀번호 확인</th>
                                                <td>
                                                    <input type="password" class="form-control" id="confirmPassword" placeholder="비밀번호 확인을 입력해주세요">
                                                </td>
                                            </tr>
                                        </table>

                                        <div class="text-center">
                                            <button type="button" class="btn btn-primary me-2" id="btnConfirm">확인</button>
                                            <button type="button" class="btn btn-secondary" id="btnCancel">취소</button>
                                        </div>
                                    </div>

                                    <!-- 가입완료 Tab -->
                                    <div class="tab-pane fade" id="complete" role="tabpanel">
                                        <div class="complete-message">
                                            <i class="ri-checkbox-circle-line"></i>
                                            <h3><?= session()->get('user_name') ?>님의 가입을 축하합니다.</h3>
                                            <button type="button" class="btn btn-primary mt-4" id="btnComplete">확인</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <?= $this->include('partials/vendor-scripts') ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const agreeCheck = document.getElementById('agreeCheck');
            const btnAgree = document.getElementById('btnAgree');
            const btnDisagree = document.getElementById('btnDisagree');
            const btnConfirm = document.getElementById('btnConfirm');
            const btnCancel = document.getElementById('btnCancel');
            const btnComplete = document.getElementById('btnComplete');
            
            const agreementTab = document.getElementById('agreement-tab');
            const passwordTab = document.getElementById('password-tab');
            const completeTab = document.getElementById('complete-tab');

            // 동의함 버튼
            btnAgree.addEventListener('click', function() {
                if (!agreeCheck.checked) {
                    alert('약관에 동의해주세요.');
                    return;
                }
                passwordTab.disabled = false;
                const tab = new bootstrap.Tab(passwordTab);
                tab.show();
            });

            // 동의안함 버튼
            btnDisagree.addEventListener('click', function() {
                if (confirm('약관에 동의하지 않으시면 서비스를 이용하실 수 없습니다. 로그인 페이지로 이동하시겠습니까?')) {
                    window.location.href = '<?= site_url('user/logout') ?>';
                }
            });

            // 확인 버튼 (비밀번호 변경)
            btnConfirm.addEventListener('click', function() {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                if (!newPassword || !confirmPassword) {
                    alert('비밀번호를 입력해주세요.');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    alert('비밀번호가 일치하지 않습니다.');
                    return;
                }

                // 비밀번호 유효성 검사 (8-12자, 영문+숫자+특수문자)
                const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,12}$/;
                if (!passwordRegex.test(newPassword)) {
                    alert('비밀번호는 8~12자리로 영문, 숫자, 특수문자를 조합하여 구성해야 합니다.');
                    return;
                }

                // AJAX로 비밀번호 변경 처리
                fetch('<?= site_url('user/updatePassword') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'password=' + encodeURIComponent(newPassword)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        completeTab.disabled = false;
                        const tab = new bootstrap.Tab(completeTab);
                        tab.show();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('오류가 발생했습니다. 다시 시도해주세요.');
                });
            });

            // 취소 버튼
            btnCancel.addEventListener('click', function() {
                if (confirm('취소하시겠습니까?')) {
                    window.location.href = '<?= site_url('user/logout') ?>';
                }
            });

            // 완료 버튼
            btnComplete.addEventListener('click', function() {
                window.location.href = '<?= site_url('user/rsvn') ?>';
            });
        });
    </script>
</body>
</html>
