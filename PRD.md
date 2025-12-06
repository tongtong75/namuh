# PRD: 검진 예약 시스템

## 📋 문서 정보
- **작성일**: 2025-12-06
- **프로젝트명**: 병원 검진 예약 관리 시스템
- **페이지**: 사용자 검진 예약 페이지
- **URL**: `/user/rsvnSel`

---

## 🎯 개요

### 목적
검진 대상자(직원 및 가족)가 병원을 선택하고, 검진 날짜와 상품을 선택하여 검진 예약을 완료하는 시스템

### 주요 기능
1. 병원 선택
2. 검진 희망일 선택 (달력)
3. 검진 상품 선택
4. 상품별 선택 항목 구성
5. 추가 검사 선택
6. 예약자 정보 입력 및 예약 완료

---

## 👥 사용자 유형

### 1. 본인 (직원)
- `RELATION = 'S'`
- 본인지원금 사용 (`SUPPORT_FUND`)
- 상품 필터링: `SPRT_SE` 컬럼 기준

### 2. 가족 (배우자, 자녀 등)
- `RELATION IN ('W', 'C', 'P', 'O')`
- 가족지원금 사용 (`FAMILY_SUPPORT_FUND`)
- 상품 필터링: `FAM_SPRT_SE` 컬럼 기준
- **중요**: 지원금 정보가 없으면 본인(직원)의 지원금 정보 조회

---

## 🔄 사용자 시나리오

### Scenario 1: 신규 예약
```
1. 검진대상자 목록에서 [예약] 버튼 클릭
   → 예약 페이지 진입 (RSVT_STTS = 'N')

2. 검진병원 선택 (Step 1)
   - 회사와 연결된 병원 목록 표시
   - 병원 선택 시 확인 알림
   - 선택된 병원은 재클릭 불가 (cursor: not-allowed)
   - 선택 시 자동으로 Step 2 탭 이동

3. 검진일/검진상품 선택 (Step 2)
   - 왼쪽: FullCalendar로 검진 가능 날짜 표시
     * 일정 있는 날짜만 클릭 가능
     * 마감된 날짜는 선택 불가
   - 오른쪽: 검진상품 목록
     * 지원금에 맞는 상품만 필터링
     * [검사항목보기] 버튼: 모달로 상세 항목 표시
     * [상품선택] 버튼: 상품 선택 영역 표시
   - 상품 선택 시 선택항목 테이블 표시
     * 성별 필터링 (남/여/공통)
     * 그룹별 선택 갯수 검증
     * 검진유형별 마감 체크
   - [다음] 버튼: 선택 갯수 검증 후 Step 3 이동

4. 추가검사 선택 (Step 3)
   - Sticky 영역: 본인부담 발생금액 실시간 표시 + [이전][다음] 버튼
   - 추가검사 항목 체크박스
   - 성별 필터링
   - 체크 시 금액 자동 계산
   - [다음] 버튼: Step 4 이동

5. 예약자 정보 확인 (Step 4)
   - 일반전화, 핸드폰 입력
   - 주소 입력 (우편번호 찾기 API 연동)
   - [완료] 버튼: 예약 저장

6. 예약 완료
   → 예약 내역 페이지로 이동
```

### Scenario 2: 예약 변경
```
1. 검진대상자 목록에서 예약 상태인 항목 [수정] 버튼 클릭
   → 예약 페이지 진입 (RSVT_STTS = 'Y' or 'C')

2. 기존 예약 정보 자동 복원
   - 병원 선택 상태 복원
   - 검진 희망일 복원 (알림 없음)
   - 검진 상품 복원 (상품명도 표시)
   - 선택 항목 체크박스 복원
   - 추가 검사 항목 복원
   - 연락처/주소 정보 복원

3. 정보 수정
   - Step 2 탭부터 시작
   - 검증 로직 건너뛰고 탭 이동만 수행
   - [다음] 버튼: 검증 없이 탭만 이동

4. 예약 변경 완료
   → 검진대상자 목록으로 이동
```

---

## 🎨 UI 구성

### 상단 예약정보 테이블 (고정)
```
┌─────────────────────────────────────────────────────┐
│ 검진자명      │ 홍길동      │ 생년월일(성별) │ 850101(남자) │
├─────────────────────────────────────────────────────┤
│ 검진병원      │ 서울병원    │ 검진희망일     │ 2025-03-15   │
├─────────────────────────────────────────────────────┤
│ 검진상품      │ 종합검진    │ 추가검사       │              │
├─────────────────────────────────────────────────────┤
│ 본인부담금    │ 100,000원   │ 관계           │ 본인         │
├─────────────────────────────────────────────────────┤
│ 본인지원금    │ 500,000원   │ 가족지원금     │ 300,000원    │
└─────────────────────────────────────────────────────┘
```

### 탭 구조
```
[검진병원 선택] → [검진일/검진상품 선택] → [추가검사 선택] → [예약자 정보 확인]
   Step 1              Step 2                  Step 3            Step 4
```

### Step 3 Sticky 영역
```
┌─────────────────────────────────────────────────────────────┐
│ 본인부담 발생금액(누적)  │  150,000원  │  [이전] [다음 →]    │
└─────────────────────────────────────────────────────────────┘
  (화면 스크롤해도 고정)
```

---

## 🔧 기술 요구사항

### 백엔드 (CodeIgniter 4 + PHP 8.1+)

#### 컨트롤러: `UserRsvnController.php`

**메서드 목록:**

1. `index()` - 초기 페이지 로드
   - 검진대상자 정보 조회
   - 지원금 정보 조회 (없으면 본인 정보 가져오기)
   - 연결된 병원 목록 조회
   - View 렌더링

2. `getCalendarEvents()` - 달력 이벤트 조회 (AJAX)
   - 입력: `hsptl_sn`, `year`
   - 출력: 검진 일정 JSON

3. `getProducts()` - 검진 상품 목록 조회 (AJAX)
   - 입력: `hsptl_sn`, `ckup_trgt_sn`
   - 처리: 지원금 정보 확인 → 상품 필터링
   - 출력: 상품 목록 JSON

4. `getCheckupItems()` - 검사항목 상세 조회 (AJAX)
   - 입력: `ckup_gds_sn`
   - 출력: 검사항목 목록 JSON

5. `getProductChoiceItems()` - 상품 선택항목 조회 (AJAX)
   - 입력: `ckup_gds_sn`
   - 출력: 선택그룹 + 항목 JSON

6. `getAdditionalCheckups()` - 추가검사 항목 조회 (AJAX)
   - 입력: `ckup_gds_sn`
   - 출력: 추가검사 목록 JSON

7. `completeReservation()` - 예약 완료 처리 (AJAX)
   - 입력: FormData (예약 정보)
   - 처리: 트랜잭션으로 다중 테이블 저장
   - 출력: 성공/실패 JSON

8. `getReservationDetails()` - 예약 상세 정보 조회 (AJAX)
   - 입력: `ckup_trgt_sn`
   - 출력: 선택항목 + 추가검사 + 연락처 + 주소 JSON

#### 데이터베이스 테이블

```sql
-- 검진대상자
CKUP_TRGT (
    CKUP_TRGT_SN,          -- PK
    BUSINESS_NUM,          -- 사번/사업자번호
    CO_SN,                 -- 회사 FK
    CKUP_YYYY,             -- 검진년도
    CKUP_NAME,             -- 검진자명
    RELATION,              -- 관계 (S:본인, W:배우자, C:자녀...)
    SEX,                   -- 성별 (M/F)
    SUPPORT_FUND,          -- 본인지원금
    FAMILY_SUPPORT_FUND,   -- 가족지원금
    CKUP_HSPTL_SN,         -- 검진병원 FK
    CKUP_RSVN_YMD,         -- 검진예약일자
    CKUP_GDS_SN,           -- 검진상품 FK
    RSVT_STTS,             -- 예약상태 (N:미예약, Y:예약, C:취소)
    TEL,                   -- 전화번호
    HANDPHONE,             -- 핸드폰
    DEL_YN
)

-- 검진상품
CKUP_GDS_EXCEL_MNG (
    CKUP_GDS_EXCEL_MNG_SN, -- PK
    HSPTL_SN,              -- 병원 FK
    CKUP_YYYY,             -- 검진년도
    CKUP_GDS_NM,           -- 상품명
    SPRT_SE,               -- 지원구분 (본인용)
    FAM_SPRT_SE,           -- 가족지원구분
    DEL_YN
)

-- 상품 선택그룹
CKUP_GDS_EXCEL_CHC_GROUP (
    CKUP_GDS_EXCEL_CHC_GROUP_SN, -- PK
    CKUP_GDS_EXCEL_MNG_SN,        -- 상품 FK
    GROUP_NM,                      -- 그룹명
    CHC_ARTCL_CNT,                 -- 선택갯수 옵션1
    CHC_ARTCL_CNT2,                -- 선택갯수 옵션2
    DEL_YN
)

-- 상품 선택항목
CKUP_GDS_EXCEL_CHC_ARTCL (
    CKUP_GDS_EXCEL_CHC_ARTCL_SN,  -- PK
    CKUP_GDS_EXCEL_CHC_GROUP_SN,  -- 그룹 FK
    CKUP_TYPE,                     -- 검사유형 (GS:위내시경, CS:대장내시경...)
    CKUP_ARTCL,                    -- 검사항목명
    GNDR_SE,                       -- 성별구분 (M/F/C)
    DEL_YN
)

-- 추가검사항목
CKUP_GDS_EXCEL_ADD_CHC (
    CKUP_GDS_EXCEL_ADD_CHC_SN,    -- PK
    CKUP_GDS_EXCEL_SN,             -- 상품 FK
    CKUP_ARTCL,                    -- 검사항목명
    CKUP_CST,                      -- 검사비용
    GNDR_SE,                       -- 성별구분 (M/F/C)
    DEL_YN
)

-- 예약 선택항목
RSVN_CKUP_GDS_CHC_ARTCL (
    RSVN_CKUP_GDS_CHC_ARTCL_SN,   -- PK
    CKUP_GDS_EXCEL_CHC_ARTCL_SN,  -- 선택항목 FK
    CKUP_TRGT_SN,                  -- 대상자 FK
    DEL_YN
)

-- 예약 추가검사
RSVN_CKUP_GDS_ADD_CHC (
    RSVN_CKUP_GDS_ADD_CHC_SN,     -- PK
    CKUP_GDS_EXCEL_ADD_ARTCL_SN,  -- 추가검사 FK
    CKUP_TRGT_SN,                  -- 대상자 FK
    DEL_YN
)

-- 예약자 주소
RSVN_CKUP_TRGT_ADDR (
    RSVN_CKUP_TRGT_ADDR_SN,       -- PK
    CKUP_TRGT_SN,                  -- 대상자 FK
    ZIP_CODE,                      -- 우편번호
    ADDR,                          -- 주소
    ADDR2                          -- 상세주소
)

-- 일일검진관리
DAY_CKUP_MNG (
    DAY_CKUP_MNG_SN,              -- PK
    HSPTL_SN,                     -- 병원 FK
    CKUP_YMD,                     -- 검진일자
    CKUP_SE,                      -- 검진구분 (TOTAL/GS/CS...)
    MAX_CNT,                      -- 최대인원수
    CKUP_SE_CHC_CNT               -- 현재선택수
)

-- 회사-병원 연결
CO_HSPTL_LNKNG (
    CO_SN,                        -- 회사 FK
    HSPTL_SN                      -- 병원 FK
)
```

### 프론트엔드 (JavaScript + jQuery)

#### 핵심 라이브러리
- **jQuery**: AJAX, DOM 조작
- **Bootstrap 5**: UI 컴포넌트, 탭
- **SweetAlert2**: 알림/확인 대화상자
- **FullCalendar 6**: 달력 UI
- **Daum 우편번호 API**: 주소 검색

#### 주요 함수

**전역 변수:**
```javascript
let selectedHospitalSn = null;  // 선택된 병원 ID
let selectedDate = null;        // 선택된 날짜
let calendar = null;            // FullCalendar 인스턴스
const rsvtStts = 'Y' or 'N';   // 예약상태 (PHP에서 전달)
```

**초기화 함수:**
1. `restoreHospitalSelection()` - 병원 선택 복원
2. `restoreDateSelection()` - 날짜 선택 복원
3. `restoreProductSelection()` - 상품 선택 복원
4. `restoreAdditionalItems()` - 추가검사 복원
5. `restoreContactInfo()` - 연락처/주소 복원

**데이터 로드 함수:**
1. `loadCalendar(hsptlSn)` - 달력 초기화 및 이벤트 로드
2. `loadProducts(hsptlSn, ckupTrgtSn, isRestore)` - 상품 목록 로드
3. `loadAdditionalCheckups(ckupGdsSn)` - 추가검사 항목 로드

**UI 처리 함수:**
1. `handleDateSelection(dateStr, element, isRestore)` - 날짜 선택 처리
2. `showCheckupItems(ckupGdsSn)` - 검사항목 모달 표시
3. `showProductChoice(ckupGdsSn, isRestore)` - 상품 선택 영역 표시
4. `renderProductChoiceTable(groups, container, savedItems)` - 선택항목 테이블 렌더링
5. `renderAdditionalCheckupTable(items, container)` - 추가검사 테이블 렌더링
6. `updateTotalAdditionalCost()` - 본인부담금 계산

**검증 함수:**
1. `addCheckboxListeners()` - 선택항목 체크박스 이벤트
   - 검진유형별 마감 체크
   - 그룹별 선택 갯수 검증 (옵션1 또는 옵션2)
2. 선택완료 시 검증
   - 모든 그룹이 정확한 갯수 선택했는지 확인

**공통 함수:**
1. `goToStep4FromAdditional()` - Step 3 → Step 4 이동
2. `goToStep2FromAdditional()` - Step 3 → Step 2 이동

---

## 🔐 비즈니스 로직

### 1. 지원금 처리 로직

```php
// 지원금 정보가 없을 경우, 본인의 지원금 정보를 가져옴
if (empty($targetInfo['SUPPORT_FUND'])) {
    $employeeInfo = $this->ckupTrgtModel
        ->where('BUSINESS_NUM', $targetInfo['BUSINESS_NUM'])
        ->where('CO_SN', $targetInfo['CO_SN'])
        ->where('CKUP_YYYY', $targetInfo['CKUP_YYYY'])
        ->where('RELATION', 'S')
        ->first();

    if ($employeeInfo) {
        $targetInfo['SUPPORT_FUND'] = $employeeInfo['SUPPORT_FUND'];
        $targetInfo['FAMILY_SUPPORT_FUND'] = $employeeInfo['FAMILY_SUPPORT_FUND'];
    }
}

// 지원구분 결정
$sprtSe = ($targetInfo['RELATION'] == 'S')
    ? $targetInfo['SUPPORT_FUND']
    : $targetInfo['FAMILY_SUPPORT_FUND'];

// 상품 필터링
if ($targetInfo['RELATION'] == 'S') {
    $products->where('SPRT_SE', $sprtSe);
} else {
    $products->where('FAM_SPRT_SE', $sprtSe);
}
```

### 2. 선택항목 검증 로직

```javascript
// 그룹별 선택 갯수 수집
const allGroups = {};
checkboxes.forEach(cb => {
    const gId = cb.getAttribute('data-group-id');
    if (!allGroups[gId]) {
        allGroups[gId] = {
            maxCount1: parseInt(cb.getAttribute('data-max-count')),
            maxCount2: parseInt(cb.getAttribute('data-max-count2')) || null,
            checkedCount: 0
        };
    }
    if (cb.checked) {
        allGroups[gId].checkedCount++;
    }
});

// 옵션1 검증 (모든 그룹 == CNT1)
let isOption1Complete = true;
for (const gId in allGroups) {
    if (allGroups[gId].checkedCount !== allGroups[gId].maxCount1) {
        isOption1Complete = false;
        break;
    }
}

// 옵션2 검증 (모든 그룹 == CNT2)
let isOption2Complete = true;
let hasOption2 = false;
for (const gId in allGroups) {
    if (allGroups[gId].maxCount2 !== null) hasOption2 = true;
    const target = allGroups[gId].maxCount2 || allGroups[gId].maxCount1;
    if (allGroups[gId].checkedCount !== target) {
        isOption2Complete = false;
        break;
    }
}

// 검증 실패
if (!isOption1Complete && !isOption2Complete) {
    Swal.fire('선택 확인', '선택갯수에 맞게 선택해주세요.', 'warning');
    return;
}
```

### 3. 검진유형별 마감 체크

```javascript
const ckupType = checkbox.getAttribute('data-ckup-type');

if (ckupTypeName && selectedDate && calendar) {
    const events = calendar.getEvents().filter(event => {
        return event.startStr === selectedDate
            && event.title.startsWith(ckupTypeName);
    });

    if (events.length > 0) {
        const capacityMatch = events[0].title.match(/(\d+)\/(\d+)/);
        if (capacityMatch) {
            const current = parseInt(capacityMatch[1]);
            const total = parseInt(capacityMatch[2]);
            if (current >= total) {
                Swal.fire('마감 안내', `${ckupTypeName} 검사가 마감되었습니다.`, 'warning');
                checkbox.checked = false;
                return;
            }
        }
    }
}
```

### 4. 초기화 로직

**병원 변경 시:**
- 검사희망일 초기화
- 선택된 상품 초기화
- 상품 선택 영역 숨김
- 본인부담금 초기화 (0원)
- 추가검사 체크박스 초기화

**상품 변경 시:**
- 본인부담금 초기화 (0원)
- 추가검사 체크박스 초기화

### 5. 예약 저장 트랜잭션

```php
$db->transStart();

try {
    // 1. CKUP_TRGT 업데이트
    $this->ckupTrgtModel->update($ckupTrgtSn, [
        'CKUP_HSPTL_SN' => $hsptlSn,
        'CKUP_RSVN_YMD' => $ckupDate,
        'CKUP_GDS_SN' => $ckupGdsSn,
        'TEL' => $tel,
        'HANDPHONE' => $handphone,
        'RSVT_STTS' => 'Y'
    ]);

    // 2. 주소 등록/수정
    if ($existingAddr) {
        $rsvnAddrModel->update($existingAddr['RSVN_CKUP_TRGT_ADDR_SN'], $addrData);
    } else {
        $rsvnAddrModel->insert($addrData);
    }

    // 3. 선택항목 등록 (기존 삭제 후 재등록)
    $rsvnChcModel->where('CKUP_TRGT_SN', $ckupTrgtSn)->delete();
    foreach ($choiceItems as $itemSn) {
        $rsvnChcModel->insert([...]);
    }

    // 4. 추가검사 등록 (기존 삭제 후 재등록)
    $rsvnAddModel->where('CKUP_TRGT_SN', $ckupTrgtSn)->delete();
    foreach ($additionalItems as $itemSn) {
        $rsvnAddModel->insert([...]);
    }

    $db->transComplete();
} catch (\Exception $e) {
    $db->transRollback();
}
```

---

## 🎯 핵심 기능 요구사항

### 1. 성별 필터링
- 선택항목 및 추가검사에서 성별 필터링 적용
- `GNDR_SE`: 'M' (남성만), 'F' (여성만), 'C' (공통)
- 사용자 성별과 일치하거나 'C'인 항목만 표시

### 2. 검진유형별 정원 관리
- 검진유형: TOTAL(전체), GS(위내시경), CS(대장내시경), CT, UT(초음파) 등
- 각 유형별 현재인원/최대인원 관리
- 마감 시 선택 불가

### 3. 복원 모드 처리
- 알림 억제: 날짜 선택 시 알림 안 뜸
- 검증 건너뛰기: 선택완료 시 검증 로직 실행 안 함
- Step 2부터 시작

### 4. 스크롤 최상단 이동
- 탭 이동 시 항상 페이지 최상단으로 스크롤
- `window.scrollTo({ top: 0, behavior: 'smooth' })`

### 5. 재클릭 방지
- 이미 선택된 병원/상품 클릭 시 아무 반응 없음
- 커서 스타일: `cursor: not-allowed`

---

## 📱 UX 요구사항

### 1. 즉각적인 피드백
- 선택 시 버튼 색상 변경 (outline → primary)
- 금액 실시간 계산 및 표시
- 알림창으로 선택 확인

### 2. 명확한 안내
- 각 단계별 안내 문구
- 필수 입력 항목 표시 (`*`)
- 경고 메시지 (마감, 선택 불가 등)

### 3. 데이터 보존
- 탭 이동 시 입력 데이터 유지
- 예약 변경 시 기존 정보 표시

### 4. 접근성
- Sticky 영역에 버튼 배치로 스크롤 불필요
- 상단 예약정보에 모든 선택 내용 요약 표시

---

## ✅ 검증 규칙

### 필수 입력 검증

**Step 2:**
- 검진희망일 선택 필수
- 검진상품 선택 필수
- 선택항목 갯수 정확히 맞춰야 함

**Step 4:**
- 핸드폰 필수
- 우편번호 필수
- 기본주소 필수

### 비즈니스 검증

**날짜 선택:**
- 검진 일정이 없는 날짜 선택 불가
- 마감된 날짜 선택 불가

**선택항목:**
- 검진유형별 마감 체크
- 그룹별 선택 갯수 검증

**금액 계산:**
- 추가검사 비용 합계 정확히 계산

---

## 🔄 상태 관리

### 예약 상태 (`RSVT_STTS`)
- `'N'`: 미예약 (신규 예약 모드)
- `'Y'`: 예약 완료 (변경 모드)
- `'C'`: 예약 취소 (변경 모드)

### 삭제 플래그 (`DEL_YN`)
- `'N'`: 활성
- `'Y'`: 삭제 (소프트 삭제)

---

## 🎨 스타일 가이드

### 버튼 상태
- 기본: `btn-outline-primary`
- 선택: `btn-primary`
- 비활성: `cursor: not-allowed`

### 색상
- Primary: `#0ab39c` (선택, 강조)
- Success: `#28a745` (완료, 본인부담금 영역)
- Warning: `#ffc107` (경고)
- Danger: `#dc3545` (오류)
- Light: `#f8f9fa` (배경)

### Sticky 영역
```css
.sticky-cost-display {
    position: sticky;
    top: 70px;
    z-index: 100;
    background-color: #f0fff0;
}
```

---

## 📊 성능 요구사항

### 응답 시간
- 페이지 로드: < 2초
- AJAX 요청: < 1초
- 달력 렌더링: < 500ms

### 최적화
- 이미지 lazy loading
- 불필요한 재조회 방지
- 트랜잭션 단위 최소화

---

## 🧪 테스트 시나리오

### 1. 신규 예약 - 정상 케이스
1. 병원 선택
2. 날짜 선택
3. 상품 선택
4. 선택항목 선택 (정확한 갯수)
5. 추가검사 선택
6. 연락처/주소 입력
7. 예약 완료 확인

### 2. 예약 변경 - 정상 케이스
1. 예약 상태 진입
2. 모든 정보 복원 확인
3. 상품 변경
4. 추가검사 변경
5. 예약 변경 완료

### 3. 에러 케이스
1. 날짜 미선택 후 상품 선택 → 경고
2. 선택항목 갯수 틀리게 선택 → 경고
3. 마감된 날짜 선택 시도 → 경고
4. 필수 입력 누락 → 경고

### 4. 초기화 테스트
1. 병원 변경 시 모든 정보 초기화 확인
2. 상품 변경 시 본인부담금 초기화 확인

---

## 📝 개발 우선순위

### Phase 1: 핵심 기능 (Must Have)
- [ ] 병원 선택
- [ ] 날짜 선택 (달력)
- [ ] 상품 선택
- [ ] 선택항목 선택
- [ ] 예약 저장

### Phase 2: 부가 기능 (Should Have)
- [ ] 추가검사 선택
- [ ] 본인부담금 계산
- [ ] 예약 변경 (복원)
- [ ] 주소 입력

### Phase 3: 개선 (Nice to Have)
- [ ] Sticky 버튼
- [ ] 재클릭 방지
- [ ] 스크롤 최상단 이동
- [ ] 알림 개선

---

## 🚀 배포 체크리스트

- [ ] 데이터베이스 마이그레이션 실행
- [ ] 환경 변수 설정 (.env)
- [ ] 외부 API 키 설정 (Daum 우편번호)
- [ ] 세션 설정 확인
- [ ] CSRF 보호 활성화
- [ ] 에러 로깅 설정
- [ ] 프론트엔드 번들링/압축
- [ ] 브라우저 호환성 테스트 (Chrome, Safari, Edge)

---

## 📞 참고 사항

### 외부 의존성
- **Daum 우편번호 API**: `//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js`
- **FullCalendar**: `https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js`
- **SweetAlert2**: `https://cdn.jsdelivr.net/npm/sweetalert2@11`

### 코딩 컨벤션
- **PHP**: CodeIgniter Coding Standard
- **JavaScript**: ES6+, camelCase
- **CSS**: BEM 방법론
- **Database**: UPPER_SNAKE_CASE

---

## 🔗 관련 문서

- [CodeIgniter 4 User Guide](https://codeigniter.com/user_guide/)
- [FullCalendar Documentation](https://fullcalendar.io/docs)
- [SweetAlert2 Documentation](https://sweetalert2.github.io/)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.0/)
- [CLAUDE.md](./CLAUDE.md) - 프로젝트 개발 가이드

---

## 📌 히스토리

| 날짜 | 버전 | 변경사항 | 작성자 |
|------|------|---------|--------|
| 2025-12-06 | 1.0 | 초안 작성 | Claude |

