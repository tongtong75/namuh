<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'일자별 검진 현황')); ?>

    <!-- fullcalendar css -->
    
    

    <?= $this->include('partials/head-css') ?>

    <style>
        .fc-daygrid-event-dot {
            display: none;
        }
        .fc-event-title {
            white-space: pre-wrap;
            padding: 2px;
        }
        .fc-event {
            cursor: pointer;
        }
        /* day header text color */
        .fc-col-header-cell.fc-day-sun a {
            color: red;
        }
        .fc-col-header-cell.fc-day-sat a {
            color: blue;
        }
        .fc-daygrid-day-number {
            white-space: nowrap;
            padding: 4px;
        }
    </style>

</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <?php echo view('partials/page-title', array('pagetitle'=>'검진 예약 관리', 'title'=>'일자별 검진 현황')); ?>

                    <div id="ajax-message-placeholder"></div>

                    <div class="row">
                        <div class="col-lg-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">검색 조건</h5>
                                </div>
                                <div class="card-body">
                                    <form id="filter-form" onsubmit="return false;">
                                        <div class="mb-3">
                                            <label for="hospital-filter" class="form-label">검진병원</label>
                                            <select id="hospital-filter" name="hsptl_sn" class="form-select">
                                                <?php if (session()->get('user_type') !== 'H'): ?>
                                                    <option value="">전체병원</option>
                                                <?php endif; ?>
                                                <?php foreach ($hospitals as $hospital): ?>
                                                    <option value="<?= esc($hospital['HSPTL_SN']) ?>"><?= esc($hospital['HSPTL_NM']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="year-filter" class="form-label">검진년도</label>
                                            <select id="year-filter" name="ckup_year" class="form-select">
                                                <?php 
                                                    $currentYear = date('Y');
                                                    for ($i = $currentYear + 1; $i >= $currentYear - 5; $i--): 
                                                ?>
                                                    <option value="<?= $i ?>" <?= ($i == $currentYear) ? 'selected' : '' ?>><?= $i ?>년</option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <button type="button" id="btn-filter-search" class="btn btn-primary w-100">검색</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-10">
                            <div class="card">
                                <div class="card-body">
                                    <div id="calendar"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--start add/edit modal-->
                    <div class="modal fade" id="event-modal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0">
                                <div class="modal-header p-3 bg-light-subtle">
                                    <h5 class="modal-title" id="modal-title">일자별 상세 설정</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div id="modal-ajax-message-placeholder"></div>
                                    <form id="event-form" name="event-form" onsubmit="return false;">
                                        <input type="hidden" id="event-date" />
                                        <div class="row mb-3 align-items-center">
                                            <label for="total-personnel" class="col-sm-3 col-form-label">전체 인원</label>
                                            <div class="col-sm-2">
                                                <input class="form-control form-control-sm" type="number" id="total-personnel" />
                                            </div>
                                        </div>

                                        <div class="row mb-2 gx-2 align-items-center">
                                            <div class="col">
                                                <select id="daily-ckup-artcl-select" class="form-select form-select-sm">
                                                    <option value="">검사구분 선택</option>
                                                    <option value="CS">대장</option>
                                                    <option value="GS">위내시경</option>
                                                    <option value="PU">골반초음파</option>
                                                    <option value="BU">유방초음파</option>
                                                    <option value="UT">초음파</option>
                                                    <option value="CT">CT</option>
                                                </select>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" id="btn-add-daily-item" class="btn btn-sm btn-primary">추가</button>
                                            </div>
                                        </div>

                                        <table id="daily-item-table" class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>검사구분</th>
                                                    <th >인원</th>
                                                    <th>관리</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">닫기</button>
                                            <button type="submit" class="btn btn-success" id="btn-save-event">저장</button>
                                        </div>
                                    </form>
                                </div>
                            </div> <!-- end modal-content-->
                        </div> <!-- end modal dialog-->
                    </div> <!-- end modal-->

                </div><!-- container-fluid -->
            </div>
            <?= $this->include('partials/footer') ?>
        </div>
    </div>
    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- fullcalendar min js -->
    <!--script src="/assets/libs/fullcalendar/index.global.min.js"></script-->
    <script src="<?= base_url('/public/assets/libs/fullcalendar/index.global.min.js') ?>"></script>

    <script>
        const BASE_URL = '<?= rtrim(site_url(), '/') . '/' ?>';
        let calendar;

        function showAjaxMessage(message, type = 'success') {
            const placeholder = $('#ajax-message-placeholder');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
            placeholder.html(alertHtml);
            setTimeout(() => { placeholder.find('.alert').alert('close'); }, 5000);
        }

        function showModalAjaxMessage(message, type = 'success') {
            const placeholder = $('#modal-ajax-message-placeholder');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
            placeholder.html(alertHtml);
        }

        function getCheckupTypeText(ckupSe) {
            switch(ckupSe) {
                case 'ET': return '기타';
                case 'CT': return 'CT';
                case 'GS': return '위내시경';
                case 'UT': return '초음파';
                case 'CS': return '대장내시경';
                case 'PU': return '골반초음파';
                case 'BU': return '유방초음파';
                default: return ckupSe;
            }
        }

        function openDetailModal(dateStr) {
            const hsptlSn = $('#hospital-filter').val();
            if (!hsptlSn) {
                showAjaxMessage('병원을 먼저 선택해주세요.', 'danger');
                return;
            }
            
            $('#modal-ajax-message-placeholder').empty();
            $('#modal-title').text(dateStr + ' 상세 설정');
            $('#event-date').val(dateStr);
            
            $.ajax({
                url: BASE_URL + 'mngr/dayCkupMng/getDailyDetail',
                type: 'GET',
                data: {
                    hsptl_sn: hsptlSn,
                    date: dateStr
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const tableBody = $('#daily-item-table tbody');
                        tableBody.empty();
                        let totalPersonnel = 0;
                        
                        response.detail.forEach(item => {
                            if (item.CKUP_SE === 'TOTAL') {
                                totalPersonnel = item.MAX_CNT;
                            } else {
                                const row = `<tr>
                                    <td>${getCheckupTypeText(item.CKUP_SE)}</td>
                                    <td><input type="number" class="form-control form-control-sm item-max-cnt" data-ckup-se="${item.CKUP_SE}" value="${item.MAX_CNT}" /></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-daily-item">삭제</button></td>
                                </tr>`;
                                tableBody.append(row);
                            }
                        });
                        $('#total-personnel').val(totalPersonnel);
                        $('#event-modal').modal('show');
                    } else {
                        showAjaxMessage(response.message, 'danger');
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'ko',
                headerToolbar: {
                    left: 'prev',
                    center: 'title',
                    right: 'next'
                },
                titleFormat: function(info) {
                    return `${info.date.year}년 ${info.date.month + 1}월`;
                },
                events: [],
                dateClick: function(info) {
                    openDetailModal(info.dateStr);
                },
                eventClick: function(arg) {
                    openDetailModal(arg.event.startStr);
                }
            });
            calendar.render();

            $('#btn-filter-search').on('click', function() {
                const hsptlSn = $('#hospital-filter').val();
                const year = $('#year-filter').val();

                if (!hsptlSn) {
                    showAjaxMessage('병원을 선택해주세요.', 'danger');
                    return;
                }

                calendar.removeAllEvents();
                calendar.addEventSource({
                    url: `${BASE_URL}mngr/dayCkupMng/getCalendarEvents?hsptl_sn=${hsptlSn}&year=${year}`,
                    failure: function() {
                        showAjaxMessage('이벤트 로딩에 실패했습니다.', 'danger');
                    }
                });
            });

            // Add item to daily table
            $('#btn-add-daily-item').on('click', function() {
                const selectedOption = $('#daily-ckup-artcl-select option:selected');
                const ckupSe = selectedOption.val();
                
                if (!ckupSe) {
                    showModalAjaxMessage('추가할 검사구분을 선택하세요.', 'danger');
                    return;
                }

                // Check for duplicates
                let isDuplicate = false;
                $('#daily-item-table tbody tr').each(function() {
                    if ($(this).find('input.item-max-cnt').data('ckup-se') === ckupSe) {
                        isDuplicate = true;
                    }
                });

                if (isDuplicate) {
                    showModalAjaxMessage('이미 추가된 항목입니다.', 'danger');
                    return;
                }

                const newRow = `<tr>
                    <td>${getCheckupTypeText(ckupSe)}</td>
                    <td><input type="number" class="form-control form-control-sm item-max-cnt" data-ckup-se="${ckupSe}" value="1" min="1" /></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-daily-item">삭제</button></td>
                </tr>`;
                $('#daily-item-table tbody').append(newRow);
            });

            // Remove item from daily table
            $('#daily-item-table').on('click', '.btn-remove-daily-item', function() {
                $(this).closest('tr').remove();
            });

            $('#event-form').on('submit', function(e) {
                e.preventDefault();
                const hsptlSn = $('#hospital-filter').val();
                const date = $('#event-date').val();
                const totalPersonnel = $('#total-personnel').val();
                const items = [];
                
                $('#daily-item-table tbody tr').each(function() {
                    const max_cnt_input = $(this).find('.item-max-cnt');
                    items.push({
                        ckup_se: max_cnt_input.data('ckup-se'),
                        max_cnt: max_cnt_input.val()
                    });
                });

                $.ajax({
                    url: BASE_URL + 'mngr/dayCkupMng/saveDailyDetail',
                    type: 'POST',
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                        hsptl_sn: hsptlSn,
                        date: date,
                        total_personnel: totalPersonnel,
                        items: items
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAjaxMessage(response.message, 'success');
                            $('#event-modal').modal('hide');
                            calendar.refetchEvents();
                        } else {
                            showModalAjaxMessage(response.message || '저장에 실패했습니다.', 'danger');
                        }
                    },
                    error: function() {
                        showModalAjaxMessage('서버 통신 오류가 발생했습니다.', 'danger');
                    }
                });
            });
        });
    </script>
    <script src="<?= base_url('/public/assets/js/app.js') ?>"></script>
</body>
</html>