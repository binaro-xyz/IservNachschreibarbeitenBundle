/**
 * Exercise
 */
/* globals IServ */
IServ.Exercise = IServ.register(function (IServ, $) {
    'use strict';

    function initialize() {
        $('#exerciseSubmissions').find('a.submission').click(function(e) {
            var $this = $(this);
            var url = $this.attr('href');
            var id = $this.data('id');

            var modal = $this.data('modal');
            if (typeof modal !== 'undefined') {
                modal.show();
            }
            else {
                IServ.Modal.createFromPage({
                    'id': 'subm' + id,
                    'size': 'lg',
                    'remote': url,
                    'pageHeader': '.page-content h1',
                    'pageBody': '.page-content .panel-body'
                }, $this);
            }

            e.preventDefault();
        });
    }

    // Public API
    return {
        init: initialize
    };

}(IServ, jQuery)); // end of IServ module
