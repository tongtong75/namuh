<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->addRedirect('/', 'user/login');

//사용자
$routes->group('user', static function ($routes) {
    //로그인 관련
    $routes->get('login', 'UserAuthController::login');         // 로그인 화면
    $routes->post('loginProc', 'UserAuthController::loginProc'); // 로그인 처리 (AJAX)
    $routes->get('logout', 'UserAuthController::logout');       // 로그아웃
    $routes->get('regist', 'UserAuthController::regist');       // 등록 페이지
    $routes->post('updatePassword', 'UserAuthController::updatePassword');
    
    // 예약 관련
    $routes->get('rsvn', 'UserAuthController::rsvn');           // 예약 페이지
    $routes->get('rsvn/getReservationDetails', 'UserRsvnController::getReservationDetails'); // 예약 상세 내역 조회
    $routes->get('rsvnSel', 'UserRsvnController::index');       // 예약 선택 페이지
    $routes->get('rsvnSel/getCalendarEvents', 'UserRsvnController::getCalendarEvents'); // 달력 이벤트
    $routes->get('rsvnSel/getProducts', 'UserRsvnController::getProducts'); // 검진상품 목록
    $routes->get('rsvnSel/getCheckupItems', 'UserRsvnController::getCheckupItems'); // 검사항목 조회
    $routes->get('rsvnSel/getProductChoiceItems', 'UserRsvnController::getProductChoiceItems'); // 상품선택 항목 조회
    $routes->get('rsvnSel/getAdditionalCheckups', 'UserRsvnController::getAdditionalCheckups'); // 추가검사 항목 조회
    $routes->get('rsvnSel/getReservationDetails', 'UserRsvnController::getReservationDetails'); // 예약 상세 조회 (복원용)
    $routes->post('rsvnSel/complete', 'UserRsvnController::completeReservation'); // 예약 완료 처리
    $routes->post('makeReservation', 'UserAuthController::makeReservation');     // 예약 처리
    $routes->post('cancelReservation', 'UserAuthController::cancelReservation'); // 예약 취소
    
    // 검진대상자 관리 (UserCkupTrgtController)
    $routes->group('ckupTrgt', static function ($routes) {
        $routes->get('/', 'UserCkupTrgtController::index');                     // 목록 뷰
        $routes->post('ajax_list', 'UserCkupTrgtController::ajax_list');        // 목록 데이터 (AJAX)
        $routes->get('ajax_get_ckup_trgt/(:num)', 'UserCkupTrgtController::ajax_get_ckup_trgt/$1'); // 단일 대상자 정보 (AJAX)
        $routes->post('ajax_create', 'UserCkupTrgtController::ajax_create');    // 신규 대상자 등록 (AJAX)
        $routes->post('ajax_update', 'UserCkupTrgtController::ajax_update');    // 대상자 정보 수정 (AJAX)
        $routes->post('ajax_delete/(:num)', 'UserCkupTrgtController::ajax_delete/$1'); // 대상자 정보 삭제 (soft delete, AJAX)
        $routes->post('ajax_reset_password/(:num)', 'UserCkupTrgtController::ajax_reset_password/$1'); //비밀번호 초기화

        // 메모 관련 라우트
        $routes->get('ajax_get_memo/(:num)', 'UserCkupTrgtController::ajax_get_memo/$1');       // 특정 대상자의 메모 조회 (ckup_trgt_sn 기준)
        $routes->post('ajax_save_memo', 'UserCkupTrgtController::ajax_save_memo');            // 메모 등록/수정 (AJAX)
        $routes->post('ajax_delete_memo/(:num)', 'UserCkupTrgtController::ajax_delete_memo/$1'); // 메모 삭제 (ckup_trgt_memo_sn 기준, AJAX)

        //엑셀 업로드
        $routes->post('excel_upload', 'UserCkupTrgtController::excel_upload');
        $routes->get('excel_download', 'UserCkupTrgtController::excel_download');
    });

    $routes->group('hsptl', static function ($routes) {
        $routes->get('/', 'HsptlMngController::user_index'); // List view
        $routes->get('ajax_list', 'HsptlMngController::ajax_list');
    });
});

// 모든 관리자 관련 라우트를 'mngr' 그룹으로 묶습니다.
$routes->group('mngr', static function ($routes) {

    //로그인 관련
    $routes->get('login', 'MngrAuthController::login');         // 로그인 화면
    $routes->post('loginProc', 'MngrAuthController::loginProc'); // 로그인 처리 (AJAX)
    $routes->get('logout', 'MngrAuthController::logout');       // 로그아웃

    // 관리자 관리 (MngrMngController)
    // 이제 URL은 mngr/mngrmng/* 형태가 됩니다.
    $routes->group('mngrMng', static function ($routes) {
        $routes->get('/', 'MngrMngController::index');
        $routes->post('ajax_list', 'MngrMngController::ajax_list');
        $routes->get('ajax_get_mngr/(:num)', 'MngrMngController::ajax_get_mngr/$1');
        $routes->post('ajax_create', 'MngrMngController::ajax_create');
        $routes->post('ajax_update', 'MngrMngController::ajax_update');
        $routes->post('ajax_delete/(:num)', 'MngrMngController::ajax_delete/$1');
    });

    // 병원관리 (HsptlMngController)
    $routes->group('hsptlMng', static function ($routes) {
        $routes->get('/', 'HsptlMngController::index'); // List view
        $routes->get('ajax_list', 'HsptlMngController::ajax_list');
        $routes->get('ajax_get_hsptl/(:num)', 'HsptlMngController::ajax_get_hsptl/$1');
        $routes->post('ajax_create', 'HsptlMngController::ajax_create');
        $routes->post('ajax_update', 'HsptlMngController::ajax_update');
        $routes->post('ajax_delete/(:num)', 'HsptlMngController::ajax_delete/$1');
    });

    //검진항목관리(CkupArtclMngController)
    /*
    $routes->group('ckupArtclMng', static function ($routes) {
        $routes->get('/', 'CkupArtclMngController::index'); // List view
        $routes->post('ajax_list', 'CkupArtclMngController::ajax_list');
        $routes->get('ajax_get_ckup_artcl/(:num)', 'CkupArtclMngController::ajax_get_ckup_artcl/$1');
        $routes->post('ajax_create', 'CkupArtclMngController::ajax_create');
        $routes->post('ajax_update', 'CkupArtclMngController::ajax_update');
        $routes->post('ajax_delete/(:num)', 'CkupArtclMngController::ajax_delete/$1');
        
    });

    // 선택항목관리 (ChcArtclMngController)
    $routes->group('chcArtclMng', static function ($routes) {
        $routes->get('/', 'ChcArtclMngController::index');                     // List view
        $routes->post('ajax_list', 'ChcArtclMngController::ajax_list');        // Get list data
        $routes->get('ajax_get_chc_artcl/(:num)', 'ChcArtclMngController::ajax_get_chc_artcl/$1'); // Get single item for edit
        $routes->post('ajax_create', 'ChcArtclMngController::ajax_create');    // Create new item
        $routes->post('ajax_update', 'ChcArtclMngController::ajax_update');    // Update existing item
        $routes->post('ajax_delete/(:num)', 'ChcArtclMngController::ajax_delete/$1'); // Delete item (soft delete)
    });
*/

    // 회사관리 (CoMngController)
    $routes->group('coMng', static function ($routes) { 
        $routes->get('/', 'CoMngController::index');                     // List view
        $routes->post('ajax_list', 'CoMngController::ajax_list');        // Get list data
        $routes->get('ajax_get_co/(:num)', 'CoMngController::ajax_get_co/$1'); // Get single item for edit
        $routes->post('ajax_create', 'CoMngController::ajax_create');    // Create new item
        $routes->post('ajax_update', 'CoMngController::ajax_update');    // Update existing item
        $routes->post('ajax_delete/(:num)', 'CoMngController::ajax_delete/$1'); // Delete item (soft delete)
        $routes->get('ajax_get_hsptls_for_linking/(:num)', 'CoMngController::ajax_get_hsptls_for_linking/$1'); // Get hospitals for a company
        $routes->post('ajax_save_hsptl_links', 'CoMngController::ajax_save_hsptl_links');     // Save links
    });

    // 검진대상자 관리 (CkupTrgtController)
    $routes->group('ckupTrgt', static function ($routes) {
        $routes->get('/', 'CkupTrgtController::index');                     // 목록 뷰
        $routes->post('ajax_list', 'CkupTrgtController::ajax_list');        // 목록 데이터 (AJAX)
        $routes->get('ajax_get_ckup_trgt/(:num)', 'CkupTrgtController::ajax_get_ckup_trgt/$1'); // 단일 대상자 정보 (AJAX)
        $routes->post('ajax_create', 'CkupTrgtController::ajax_create');    // 신규 대상자 등록 (AJAX)
        $routes->post('ajax_update', 'CkupTrgtController::ajax_update');    // 대상자 정보 수정 (AJAX)
        //$routes->post('ajax_update/(:num)', 'CkupTrgtController::ajax_update/$1'); // 대상자 정보 수정 (AJAX)
        $routes->post('ajax_delete/(:num)', 'CkupTrgtController::ajax_delete/$1'); // 대상자 정보 삭제 (soft delete, AJAX)
        $routes->post('ajax_reset_password/(:num)', 'CkupTrgtController::ajax_reset_password/$1'); //비밀번호 초기화

        // 메모 관련 라우트
        $routes->get('ajax_get_memo/(:num)', 'CkupTrgtController::ajax_get_memo/$1');       // 특정 대상자의 메모 조회 (ckup_trgt_sn 기준)
        $routes->post('ajax_save_memo', 'CkupTrgtController::ajax_save_memo');            // 메모 등록/수정 (AJAX)
        $routes->post('ajax_delete_memo/(:num)', 'CkupTrgtController::ajax_delete_memo/$1'); // 메모 삭제 (ckup_trgt_memo_sn 기준, AJAX)

        //엑셀 업로드
        $routes->post('excel_upload', 'CkupTrgtController::excel_upload');
        $routes->get('excel_download', 'CkupTrgtController::excel_download');
    });

    // 상품관리 (CkupGdsController)
   /* $routes->group('ckupGdsMng', static function ($routes) { 
        $routes->get('/', 'CkupGdsController::index');         
        $routes->get('add', 'CkupGdsController::add');   
        $routes->post('ckupGdsSave', 'CkupGdsController::ckupGdsSave');
        $routes->post('ajax_list', 'CkupGdsController::ajax_list');
        $routes->get('edit/(:num)', 'CkupGdsController::edit/$1');
        $routes->post('delete/(:num)', 'CkupGdsController::delete/$1');
    });
*/
    // 검진상품 엑셀 관리 (CkupGdsExcelController)
    $routes->group('ckupGdsExcel', static function ($routes) {
        $routes->get('/', 'CkupGdsExcelController::index');
        $routes->post('ajax_list', 'CkupGdsExcelController::ajax_list');
        $routes->get('add', 'CkupGdsExcelController::add');
        $routes->get('edit/(:num)', 'CkupGdsExcelController::edit/$1');
        $routes->post('save', 'CkupGdsExcelController::save');
        $routes->post('delete/(:num)', 'CkupGdsExcelController::delete/$1');
        $routes->post('deleteItem/(:segment)/(:num)', 'CkupGdsExcelController::deleteItem/$1/$2');
        $routes->post('deleteGroup/(:num)', 'CkupGdsExcelController::deleteGroup/$1');
        $routes->post('deleteItems/(:segment)', 'CkupGdsExcelController::deleteItems/$1');
        $routes->post('updateChoiceItem/(:num)', 'CkupGdsExcelController::updateChoiceItem/$1');
        $routes->post('updateBasicItem/(:num)', 'CkupGdsExcelController::updateBasicItem/$1');
        $routes->post('updateAddChoiceItem/(:num)', 'CkupGdsExcelController::updateAddChoiceItem/$1');
        $routes->post('copy/(:num)', 'CkupGdsExcelController::copy/$1');
    });

    // 요일별 검진 인원 관리 (DayCkupMngController)
    $routes->group('dayCkupMng', static function ($routes) {
        $routes->get('/', 'DayCkupMngController::index');
        $routes->get('getList', 'DayCkupMngController::getList');
        $routes->get('form', 'DayCkupMngController::form');
        $routes->get('getArticleList', 'DayCkupMngController::getArticleList');
        $routes->post('save', 'DayCkupMngController::save');
        $routes->post('delete', 'DayCkupMngController::delete');
        $routes->get('getDetail', 'DayCkupMngController::getDetail');
        
        // Calendar routes
        $routes->get('calendar', 'DayCkupMngController::calendar');
        $routes->get('getCalendarEvents', 'DayCkupMngController::getCalendarEvents');
        $routes->get('getDailyDetail', 'DayCkupMngController::getDailyDetail');
        $routes->post('saveDailyDetail', 'DayCkupMngController::saveDailyDetail');
    });

});