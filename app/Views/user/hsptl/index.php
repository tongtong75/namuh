<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'병원정보')); ?>

    <?= $this->include('partials/head-css') ?>

    

</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?= $this->include('partials/userMenu') ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php echo view('partials/page-title', array('pagetitle'=>'병원정보', 'title'=>'병원 목록')); ?>



                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <table id="hsptlList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>지역</th>
                                                <th>병원명</th>
                                                <th>담당자</th>
                                                <th>연락처</th>
                                                <th>등록일</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->

                </div><!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?= $this->include('partials/customizer') ?>

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script>
        const BASE_URL = '<?= rtrim(site_url(), '/') . '/' ?>';
        let mainTable;

        function initializeMainDataTable() {
            mainTable = $('#hsptlList').DataTable({
                ajax: {
                    url: BASE_URL + 'user/hsptl/ajax_list',
                    type: 'GET',
                    data: function(d) {
                    },
                    dataSrc: 'data',
                    dataFilter: function(data) {
                        const json = JSON.parse(data);
                        return JSON.stringify({ data: json.data });
                    }
                },
                columns: [
                    { title: "No.", className: "text-end" },
                    { title: "지역", className: "text-center" },
                    { title: "병원명", className: "text-center" },
                    { title: "담당자", className: "text-center" },
                    { title: "연락처", className: "text-center" },
                    { title: "등록일", className: "text-center" }
                ],
                dom: "<'row'<'col-sm-12 col-md-6'><'col-sm-12 col-md-6'>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [],
                language: {
                    "emptyTable": "표시할 데이터가 없습니다.",
                    "info": "총 _TOTAL_개 항목 중 _START_에서 _END_까지 표시",
                    "lengthMenu": "페이지당 _MENU_ 항목 표시",
                    "search": "검색:",
                    "paginate": {
                        "first": "처음",
                        "last": "마지막",
                        "next": "다음",
                        "previous": "이전"
                    }
                },
                responsive: true,
                order: [[0, 'asc']],
                ordering: false
            });
        }

        $(document).ready(function() {
            initializeMainDataTable();
        });
    </script>

    <!-- App js -->
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>

</html>