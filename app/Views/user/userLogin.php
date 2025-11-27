<!DOCTYPE html>
<html lang="ko">
    <meta charset="utf-8" />
    <title>로그인 | 비에비스 나무병원</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="진료당일 위·대장내시경, 소화기질환, 단일통로 복강경 수술센터, 유방·갑상선센터, 건강검진센터" name="description" />
    <meta content="vievis namuh" name="author" />
    <link rel="shortcut icon" href="<?= base_url('assets/images/favicon.png') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIEVIS NAMUH 로그인</title>
    <?= $this->include('partials/head-css') ?>
    <style>
        html, body {
            overflow: hidden;
            height: 100%;
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
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card overflow-hidden card-bg-fill galaxy-border-none">
                            <div class="row g-0">
                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4 auth-one-bg h-100">
                                        <div class="bg-overlay"></div>
                                        <div class="position-relative h-100 d-flex flex-column">
                                            <div class="mb-4">
                                                <h2 class="mb-sm-0 text-white">VIEVIS NAMUH</h2>
                                            </div>
											<div class="mb-4"></div>
                                            <div class="mb-4 fs-16 text-white">
                                                기업임직원 검진관리서비스는 체계적인 통합관리를 통해 <br/>검진업무의 효율을 높이고, 검진시행에 따른 직원의 만족도를<br/>
												향상시킴과 동시에 건강검진의 목적인 질병예방 및 조기 발견을<br/>할 수 있도록 검진후 관리 서비스를 제공함으로써,<br/>기업의 업무생산성 향상과 의료비용 감소, 임직원의 건강증진 및 건강 관리를 위한 TOTAL HEALTHCARE 솔루션입니다.
                                            </div>
											<div class="mb-4 fs-24 text-white">
											<i class="ri-phone-line">&nbsp;02-519-8936</i>
											</div>
											<div class="mb-4 fs-16 text-white">
											상담시간 : 평일(월~금) 09시30분~17시(토,일/공휴일은 휴무)<br/>
											홈페이지 이용 문의 : 02-519-8936<br/>
											예약 관련 문의 : 각 병원 대표번호<br/>
											점심시간 : (12시~13시)
											</div>
											
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->

                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4">
                                        <div>
                                            <h2 class="text-primary" style="font-weight: bold;">임직원 검진관리시스템</h2>
                                            <p class="text-muted">검진업무의 효율성 증대와 기업의 업무생산성 향상과 의료비용 감소, 임직원의 건강증진 및 건강관리를 위한 TOTAL HEALTH CARE 솔루션</p>
                                        </div>

                                        <div class="mt-4">
                                            <?php if (session()->getFlashdata('error')): ?>
                                                <div class="alert alert-danger" role="alert">
                                                    <?= session()->getFlashdata('error') ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (session()->getFlashdata('message')): ?>
                                                <div class="alert alert-success" role="alert">
                                                    <?= session()->getFlashdata('message') ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?= form_open('user/loginProc') ?>
												<div class="mb-3">
                                                    <label for="examYear" class="form-label fs-15" style="font-weight: bold;">년도</label>
													<select name="examYear" id="examYear" class="form-select mb-3" aria-label="Default select example" required>
														<?php foreach ($years as $year): ?>
															<option value="<?= $year ?>"><?= $year ?></option>
														<?php endforeach; ?>
													</select>
                                                </div>
												<div class="mb-3">
                                                    <label for="companyCode" class="form-label fs-15" style="font-weight: bold;">소속기업</label>
                                                    <select name="companyCode" id="companyCode" class="form-select mb-3" required>
														<option value="">선택</option>
														<?php foreach ($companies as $company): ?>
															<option value="<?= $company['CO_SN'] ?>"><?= esc($company['CO_NM']) ?></option>
														<?php endforeach; ?>
													</select>
                                                </div>
												
                                                <div class="mb-3">
                                                    <label for="username" class="form-label fs-15" style="font-weight: bold;">아이디</label>
                                                    <input type="text" class="form-control" id="username" name="username" placeholder="아이디(사원번호)를 입력하세요" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fs-15" for="password-input" style="font-weight: bold;">비밀번호</label>
                                                    <div class="position-relative auth-pass-inputgroup mb-3">
                                                        <input type="password" class="form-control pe-5 password-input" placeholder="비밀번호를 입력하세요" id="password-input" name="password" required>
                                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                                    </div>
                                                </div>

                                                <div class="form-check">
                                                    최초 로그인시 생년월일(주민번호 앞 6자리) 입력 후 비밀번호 변경 후에는 변경된 비밀번호로 로그인
                                                </div>

                                                <div class="mt-4 d-flex gap-2">
													<div class="w-50">
														<button class="btn btn-secondary w-100" type="submit">로그인</button>
													</div>
													<div class="w-50">
														<button class="btn btn-success w-100" type="button">비밀번호 변경</button>
													</div>
                                                </div>
                                            <?= form_close() ?>
                                        </div>

                                        <div class="mt-5 text-center">
                                            <p class="mb-0">
												<a href="<?= site_url('mngr/login') ?>" class="fw-semibold text-primary text-decoration-underline"> 병원관리자 </a>
											</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->
                            </div>
                            <!-- end row -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer galaxy-border-none">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0">&copy;
                                COPYRIGHT @<script>document.write(new Date().getFullYear())</script> VIEVIS NAMUH ALL RIGHTS RESERVED.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->
</body>
</html>