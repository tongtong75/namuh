<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});



// --- 모든 DB 쿼리를 로깅하기 위한 이벤트 추가 ---
if (ENVIRONMENT === 'development') { // 개발 환경에서만 실행되도록 조건 추가 (선택 사항)
    Events::on('DBQuery', static function (Query $query) {
        // Query 객체에서 쿼리 문자열 가져오기
        $sql = $query->getQuery();

        // 실행 시간도 로깅할 수 있습니다.
        $duration = $query->getDuration() * 1000; // milliseconds

        log_message('debug', "DB Query: " . $sql . " | Duration: " . round($duration, 3) . "ms");

        // 바인딩된 값도 보고 싶다면 (CI4 Query 객체는 getQuery()가 이미 바인딩된 쿼리를 반환)
        // log_message('debug', "Original Query for Events: " . $query->getOriginalQuery());
        // log_message('debug', "Binds for Events: " . json_encode($query->getBinds()));
    });
}


