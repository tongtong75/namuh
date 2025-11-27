# 기술 스택 및 코딩 규칙 정의서

## 📋 프로젝트 개요

이 프로젝트는 **CodeIgniter 4** 기반의 PHP 웹 애플리케이션으로, 병원 검진 관리 시스템을 구현한 프로젝트입니다.

---

## 🛠 기술 스택

### 백엔드 (Backend)

#### 핵심 프레임워크
- **CodeIgniter 4** (v4.x)
  - PHP 풀스택 웹 프레임워크
  - MVC 아키텍처 패턴
  - RESTful API 지원

#### 프로그래밍 언어
- **PHP 8.1 이상** (PHP 8.2+ 권장)
  - 타입 힌팅 (Type Hints) 사용
  - 네임스페이스 활용
  - PSR-4 오토로딩

#### 필수 PHP 확장
- `ext-intl` - 국제화 지원
- `ext-mbstring` - 멀티바이트 문자열 처리
- `ext-json` - JSON 처리
- `ext-mysqli` - MySQL 데이터베이스 연결

#### 데이터베이스
- **MySQL** (MySQLi 드라이버)
  - 문자셋: UTF-8
  - 콜레이션: utf8_general_ci
  - 날짜 형식: `Y-m-d`, `Y-m-d H:i:s`

#### 주요 라이브러리
- **PHPSpreadsheet** (^4.4) - 엑셀 파일 처리
- **Laminas Escaper** (^2.14) - XSS 방지 이스케이핑
- **PSR Log** (^3.0) - 로깅 인터페이스

#### 개발 도구
- **PHPUnit** (^10.5.16 || ^11.2) - 단위 테스팅
- **PHP CS Fixer** (^3.47.1) - 코드 포맷팅
- **CodeIgniter Coding Standard** (^1.7) - 코딩 표준
- **Kint** (^6.0) - 디버깅 도구
- **Faker** (^1.24) - 테스트 데이터 생성
- **Predis** (^1.1 || ^2.3) - Redis 클라이언트

### 프론트엔드 (Frontend)

#### 템플릿
- **Velzon Admin & Dashboard Template** (v4.3.0)
  - Bootstrap 기반 관리자 템플릿
  - 반응형 디자인

#### CSS 프레임워크
- **Bootstrap** (최신 버전)
  - RTL (Right-to-Left) 지원
  - 커스텀 SCSS 파일 사용

#### JavaScript 라이브러리
- **jQuery** - DOM 조작 및 AJAX
- **DataTables** - 테이블 데이터 표시 및 검색
- **Chart.js** - 차트 및 그래프
- **FullCalendar** - 캘린더 UI
- **Swiper** - 슬라이더/캐러셀
- **Choices.js** - 선택 박스 UI
- **SortableJS** - 드래그 앤 드롭 정렬
- **CKEditor 5** - 리치 텍스트 에디터
- **ECharts** - 데이터 시각화
- **Moment.js** - 날짜/시간 처리
- **SweetAlert2** - 알림 모달
- **Toastify** - 토스트 알림

#### 아이콘
- **Boxicons**
- **Remix Icon**
- **Material Design Icons**
- **Line Awesome**

---

## 📐 코딩 규칙 및 표준

### PHP 코딩 규칙

#### 1. 네임스페이스 및 클래스 구조

```php
<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\ExampleModel;

class ExampleController extends BaseController
{
    // 클래스 내용
}
```

**규칙:**
- 모든 클래스는 적절한 네임스페이스를 가져야 함
- `App\Controllers`, `App\Models`, `App\Filters` 등 표준 네임스페이스 사용
- 모든 클래스는 PSR-4 오토로딩 규칙을 따라야 함

#### 2. 타입 힌팅

```php
// ✅ 권장
public function attemptLogin(string $mngrId, string $password): array|false
{
    // 메서드 내용
}

protected MngrMngModel $mngrModel;
protected HsptlMngModel $hsptlModel;

// ❌ 비권장
public function attemptLogin($mngrId, $password)
{
    // 타입 힌팅 없음
}
```

**규칙:**
- 모든 메서드 파라미터와 반환 타입에 타입 힌팅 사용
- PHP 8.1+의 유니온 타입 (`array|false`) 활용
- 클래스 프로퍼티에 타입 선언 사용

#### 3. 네이밍 컨벤션

**클래스명:**
- PascalCase 사용
- 컨트롤러: `{기능}Controller` (예: `MngrMngController`)
- 모델: `{테이블명}Model` (예: `MngrMngModel`)
- 필터: `{기능}Guard` (예: `AuthGuard`)

**메서드명:**
- camelCase 사용
- 컨트롤러 메서드: 동사로 시작 (예: `ajax_list`, `ajax_create`, `ajax_update`)
- AJAX 메서드: `ajax_` 접두사 사용

**변수명:**
- camelCase 사용 (예: `$mngrId`, `$userData`)
- 데이터베이스 컬럼명: 대문자 스네이크 케이스 (예: `MNGR_ID`, `HSPTL_SN`)

**상수:**
- 대문자 스네이크 케이스 (예: `DEL_YN`, `HSPTL_SN`)

#### 4. 코드 포맷팅

**들여쓰기:**
- 4개의 스페이스 사용 (탭 금지)

**중괄호:**
```php
// ✅ 권장 - K&R 스타일
if ($condition) {
    // 코드
} else {
    // 코드
}

// ❌ 비권장
if ($condition)
{
    // 코드
}
```

**배열:**
```php
// ✅ 권장 - 짧은 배열 문법
$data = [
    'key1' => 'value1',
    'key2' => 'value2',
];

// ❌ 비권장
$data = array(
    'key1' => 'value1',
    'key2' => 'value2',
);
```

#### 5. 보안 규칙

**XSS 방지:**
```php
// ✅ 권장
echo esc($userInput);
$data['name'] = esc($row['MNGR_NM']);

// ❌ 비권장
echo $userInput; // XSS 취약점
```

**CSRF 보호:**
```php
// AJAX 요청 시 CSRF 토큰 포함
return $this->response->setJSON([
    'data' => $data,
    'csrf_hash' => csrf_hash()
]);
```

**비밀번호 처리:**
```php
// ✅ 권장 - password_hash() 사용
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ✅ 권장 - password_verify() 사용
if (password_verify($password, $hashedPassword)) {
    // 로그인 성공
}
```

**SQL Injection 방지:**
```php
// ✅ 권장 - Query Builder 사용
$builder = $this->model->where('id', $id)->first();

// ✅ 권장 - 파라미터 바인딩
$builder->like('name', $searchKeyword);

// ❌ 비권장 - 직접 쿼리 작성 시 주의
$query = "SELECT * FROM table WHERE id = $id"; // 위험!
```

#### 6. 주석 및 문서화

**PHPDoc 주석:**
```php
/**
 * 사용자의 아이디와 비밀번호를 검증합니다.
 *
 * @param string $mngrId 관리자 ID
 * @param string $password 비밀번호
 * @return array|false 로그인 성공 시 사용자 데이터 배열, 실패 시 false
 */
public function attemptLogin(string $mngrId, string $password): array|false
{
    // 메서드 내용
}
```

**인라인 주석:**
```php
// 한국어 주석 허용 (프로젝트 특성상)
// 1. 아이디로 관리자 정보 조회
$manager = $this->mngrModel->where('MNGR_ID', $mngrId)->first();

if (!$manager) {
    return false; // 해당 아이디의 관리자 없음
}
```

#### 7. 에러 처리

```php
// ✅ 권장
if (!$this->request->isAJAX()) {
    return $this->response->setStatusCode(403)->setJSON([
        'status' => 'error',
        'message' => '잘못된 요청입니다.',
        'csrf_hash' => csrf_hash()
    ]);
}

// ✅ 권장 - 예외 처리
try {
    $result = $this->model->save($data);
} catch (\Exception $e) {
    log_message('error', $e->getMessage());
    return $this->response->setStatusCode(500)->setJSON([
        'status' => 'error',
        'message' => '처리 중 오류가 발생했습니다.'
    ]);
}
```

#### 8. 데이터베이스 작업

**Query Builder 사용:**
```php
// ✅ 권장
$builder = $this->model
    ->select('MNGR_MNG.*, HSPTL_MNG.HSPTL_NM')
    ->join('HSPTL_MNG', 'HSPTL_MNG.HSPTL_SN = MNGR_MNG.HSPTL_SN')
    ->where('DEL_YN', 'N')
    ->orderBy('MNGR_SN', 'ASC')
    ->findAll();

// 검색 조건
if (!empty($searchKeyword)) {
    $builder->groupStart();
    $builder->like('HSPTL_NM', $searchKeyword);
    $builder->orLike('MNGR_NM', $searchKeyword);
    $builder->groupEnd();
}
```

**Soft Delete 패턴:**
```php
// 삭제 플래그 사용 (DEL_YN)
$hospitals = $this->model->where('DEL_YN', 'N')->findAll();
```

### JavaScript 코딩 규칙

#### 1. 네이밍 컨벤션

**변수 및 함수:**
- camelCase 사용
```javascript
// ✅ 권장
var searchKeyword = '';
function getManagerList() { }
var managerData = {};

// ❌ 비권장
var search_keyword = '';
function get_manager_list() { }
```

**상수:**
- UPPER_SNAKE_CASE 사용
```javascript
const MAX_RETRY_COUNT = 3;
const API_BASE_URL = '/mngr';
```

#### 2. 코드 포맷팅

**들여쓰기:**
- 탭 또는 4개 스페이스 (프로젝트 일관성 유지)

**세미콜론:**
- 모든 문장 끝에 세미콜론 사용
```javascript
// ✅ 권장
var x = 1;
function test() { }

// ❌ 비권장
var x = 1
function test() { }
```

#### 3. AJAX 요청 패턴

```javascript
// ✅ 권장 - jQuery AJAX
$.ajax({
    url: '/mngr/mngrMng/ajax_list',
    type: 'POST',
    data: {
        search_keyword: keyword,
        csrf_test_name: $('input[name=csrf_test_name]').val()
    },
    dataType: 'json',
    success: function(response) {
        if (response.status === 'success') {
            // 처리 로직
        }
    },
    error: function(xhr, status, error) {
        console.error('Error:', error);
    }
});
```

#### 4. 이벤트 핸들러

```javascript
// ✅ 권장 - 이벤트 위임 사용
$(document).on('click', '.btn-delete', function() {
    var id = $(this).data('id');
    // 처리 로직
});

// ✅ 권장 - 즉시 실행 함수로 스코프 분리
(function() {
    'use strict';
    // 코드
})();
```

### HTML/View 코딩 규칙

#### 1. 파일 구조

```
app/Views/
├── mngr/
│   ├── mngr_mng/
│   │   ├── index.php
│   │   └── action_buttons.php
│   └── hsptl_mng/
│       └── index.php
└── auth/
    └── mngrLogin.php
```

#### 2. 보안

```php
<!-- ✅ 권장 - XSS 방지 -->
<?= esc($data['name']) ?>

<!-- ✅ 권장 - CSRF 토큰 -->
<?= csrf_field() ?>

<!-- ✅ 권장 - 폼 검증 -->
<?= validation_list_errors() ?>
```

#### 3. CodeIgniter 헬퍼 사용

```php
<!-- ✅ 권장 -->
<?= base_url('assets/css/app.css') ?>
<?= site_url('mngr/mngrMng') ?>
<?= form_open('auth/attemptLogin') ?>
```

### 라우팅 규칙

#### 1. 라우트 그룹화

```php
// ✅ 권장 - 기능별 그룹화
$routes->group('mngr', static function ($routes) {
    $routes->group('mngrMng', static function ($routes) {
        $routes->get('/', 'MngrMngController::index');
        $routes->post('ajax_list', 'MngrMngController::ajax_list');
        $routes->get('ajax_get_mngr/(:num)', 'MngrMngController::ajax_get_mngr/$1');
        $routes->post('ajax_create', 'MngrMngController::ajax_create');
        $routes->post('ajax_update', 'MngrMngController::ajax_update');
        $routes->post('ajax_delete/(:num)', 'MngrMngController::ajax_delete/$1');
    });
});
```

#### 2. RESTful 패턴

- `GET /resource` - 목록 조회
- `GET /resource/(:num)` - 단일 항목 조회
- `POST /resource/ajax_create` - 생성
- `POST /resource/ajax_update` - 수정
- `POST /resource/ajax_delete/(:num)` - 삭제
- `POST /resource/ajax_list` - AJAX 목록 조회

### 파일 및 디렉토리 구조

#### 1. 표준 디렉토리 구조

```
source/
├── app/
│   ├── Config/          # 설정 파일
│   ├── Controllers/      # 컨트롤러
│   ├── Models/          # 모델
│   ├── Views/           # 뷰 파일
│   ├── Filters/         # 필터
│   ├── Helpers/         # 헬퍼 함수
│   ├── Libraries/       # 커스텀 라이브러리
│   └── Database/        # 마이그레이션 및 시드
├── public/              # 공개 접근 파일
│   ├── assets/         # 정적 자원 (CSS, JS, 이미지)
│   └── index.php       # 진입점
├── system/             # CodeIgniter 코어
├── tests/              # 테스트 파일
├── vendor/             # Composer 의존성
└── writable/           # 쓰기 가능한 디렉토리
    ├── cache/
    ├── logs/
    └── session/
```

#### 2. 파일명 규칙

- **컨트롤러**: `{기능}Controller.php` (예: `MngrMngController.php`)
- **모델**: `{테이블명}Model.php` (예: `MngrMngModel.php`)
- **뷰**: 소문자 스네이크 케이스 (예: `mngr_mng/index.php`)
- **필터**: `{기능}Guard.php` (예: `AuthGuard.php`)

---

## 🔧 개발 환경 설정

### 필수 요구사항

1. **PHP 8.1 이상**
2. **Composer** - 의존성 관리
3. **MySQL 5.7 이상**
4. **웹 서버** (Apache/Nginx)

### 개발 도구 설정

#### PHP CS Fixer 설정

프로젝트 루트에 `.php-cs-fixer.dist.php` 파일 생성:

```php
<?php

use CodeIgniter\CodingStandard\CodeIgniter4;
use Nexus\CsConfig\Factory;

return Factory::create(new CodeIgniter4())->forProjects();
```

#### 코드 포맷팅 실행

```bash
# 코드 포맷팅
vendor/bin/php-cs-fixer fix app/

# 코드 검사만 (수정하지 않음)
vendor/bin/php-cs-fixer fix app/ --dry-run --diff
```

#### PHPUnit 테스트 실행

```bash
# 모든 테스트 실행
composer test

# 또는
vendor/bin/phpunit
```

---

## 📝 코딩 체크리스트

### 코드 작성 전

- [ ] 타입 힌팅이 모든 메서드에 적용되었는가?
- [ ] PHPDoc 주석이 작성되었는가?
- [ ] 네이밍 컨벤션이 준수되었는가?

### 보안 체크

- [ ] XSS 방지를 위해 `esc()` 함수를 사용했는가?
- [ ] CSRF 토큰이 포함되었는가?
- [ ] SQL Injection 방지를 위해 Query Builder를 사용했는가?
- [ ] 비밀번호는 `password_hash()`로 해싱했는가?

### 코드 품질

- [ ] 코드가 PHP CS Fixer 규칙을 준수하는가?
- [ ] 불필요한 주석이나 디버그 코드가 제거되었는가?
- [ ] 에러 처리가 적절히 구현되었는가?

### 테스트

- [ ] 단위 테스트가 작성되었는가?
- [ ] AJAX 요청에 대한 검증이 구현되었는가?

---

## 🚀 배포 체크리스트

- [ ] `.env` 파일이 프로덕션 설정으로 업데이트되었는가?
- [ ] `app/Config/Database.php`에 실제 DB 정보가 하드코딩되지 않았는가?
- [ ] 디버그 모드가 비활성화되었는가?
- [ ] 로그 레벨이 적절히 설정되었는가?
- [ ] 캐시가 활성화되었는가?

---

## 📚 참고 자료

- [CodeIgniter 4 사용자 가이드](https://codeigniter.com/user_guide/)
- [PHP The Right Way](https://phptherightway.com/)
- [PSR-4 오토로딩 표준](https://www.php-fig.org/psr/psr-4/)
- [CodeIgniter Coding Standard](https://github.com/codeigniter/coding-standard)

---

## 📌 주요 약어 및 용어

- **MNGR**: Manager (관리자)
- **HSPTL**: Hospital (병원)
- **CKUP**: Checkup (검진)
- **TRGT**: Target (대상자)
- **GDS**: Goods (상품)
- **ARTCL**: Article (항목)
- **CHC**: Choice (선택)
- **CO**: Company (회사)
- **SN**: Serial Number (일련번호)
- **DEL_YN**: Delete Yes/No (삭제 여부)

---

**문서 버전**: 1.0  
**최종 업데이트**: 2024년  
**작성자**: 프로젝트 팀



