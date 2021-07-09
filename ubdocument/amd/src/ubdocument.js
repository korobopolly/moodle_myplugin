define([
    'jquery',
    'core/modal_factory',
    'core/modal_events',
    'core/templates',
    'local_ubdocument/sweetalert',
    'local_ubdocument/highcharts'
], function(
    $,
    ModalFactory,
    ModalEvents,
    Templates,
    swal,
    Highcharts
) {
 
    var ubdocument = {
        pluginname: 'local_ubdocument'
    };
    
    /**
     * DASHBOARD
     * @param {type} graph_modules
     * @returns {undefined}
     */
    ubdocument.index = function(graph_modules) {
        
    }
    
    /**
     * 테이블정의서
     * @returns {undefined}
     */
    ubdocument.table_definition = function() {
        
        $('select.form-auto-sumit').on('change', function(e) {
            $('form#form-search').submit();
        });
        
        $('a#table-init').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            swal.fire({
                type: 'warning',
                title: '요청 확인',
                html: '데이터베이스에서 스키마정보를 추출하여 적용합니다.',
                footer: '- 오랫동안 완료되지 않을 경우 새로고침(F5)하세요. -',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                confirmButtonText: '계속진행',
                cancelButtonColor: '#d33',
                cancelButtonText: '취소'
              }).then(function(result) {
                if (result.value) {
                    location.replace(url);
                }
            });
        });
        
        $('.btn-save-schema').on('click', function(e) {
            e.preventDefault();
            $('form#form-table-definition').submit();
        });
    }
    
    return ubdocument;
});