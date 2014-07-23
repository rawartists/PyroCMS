(function($) {

    $(function(){

        Pyro.GenerateSlug('input[name="field_name"]', 'input[name="field_slug"]', '_', true);

        Pyro.streams = Pyro.streams || {};

        Pyro.streams.fields = {

            build_parameters : function(field_type) {

                $.ajax({
                    dataType: 'text',
                    type: 'POST',
                    data: 'data='+field_type+'&csrf_hash_name='+$.cookie(Pyro.csrf_cookie_name),
                    url:  SITE_URL+'streams_core/ajax/build_parameters',
                    success: function(returned_html){
                        $('.streams_param_input').remove();
                        $('#parameters').append(returned_html);
                        $('select:not(.skip):not(.selectized)').selectize();
                    }
                });

            }

        }

        $(document).ready(function() {
            $('.input :input:visible:first').focus();

            $field_type = $('#field_type');

            $field_type.change(function() {

                var field_type = $(this).val();

                Pyro.streams.fields.build_parameters(field_type);

            });

        });

    });

})(jQuery);