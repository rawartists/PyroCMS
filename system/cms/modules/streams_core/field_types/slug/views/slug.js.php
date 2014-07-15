<script>
    $(document).ready(function(){
        /**
         * Generate a slug from text
         */
        Pyro.GenerateSlug = Pyro.GenerateSlug || function (input_form, output_form, space_character, disallow_dashes) {

            var slug, value;

            $(document).on('keyup', input_form, function () {

                value = $(input_form).val();

                if (value == '') return;

                space_character = space_character || '-';
                disallow_dashes = disallow_dashes || false;
                var rx = /[a-z]|[A-Z]|[0-9]|[áàâąбćčцдđďéèêëęěфгѓíîïийкłлмñńňóôóпúùûůřšśťтвýыžżźзäæœчöøüшщßåяюжαβγδεέζηήθιίϊκλμνξοόπρστυύϋφχψωώ]/,
                    value = value.toLowerCase(),
                    chars = Pyro.foreign_characters,
                    space_regex = new RegExp('[' + space_character + ']+', 'g'),
                    space_regex_trim = new RegExp('^[' + space_character + ']+|[' + space_character + ']+$', 'g'),
                    search, replace;


                // If already a slug then no need to process any further
                if (!rx.test(value)) {
                    slug = value;
                } else {
                    value = $.trim(value);

                    if (chars !== undefined) {
                        for (var i = chars.length - 1; i >= 0; i--) {
                            // Remove backslash from string
                            search = chars[i].search.replace(new RegExp('/', 'g'), '');
                            replace = chars[i].replace;

                            // create regex from string and replace with normal string
                            value = value.replace(new RegExp(search, 'g'), replace);
                        }
                        ;
                    }

                    slug = value.replace(/[^-a-z0-9~\s\.:;+=_]/g, '')
                        .replace(/[\s\.:;=+]+/g, space_character)
                        .replace(space_regex, space_character)
                        .replace(space_regex_trim, '');

                    // Remove the dashes if they are
                    // not allowed.
                    if (disallow_dashes) {
                        slug = slug.replace(/-+/g, '_');
                    }
                }

                $(output_form).val(slug);
            });
        }

        Pyro.GenerateSlug('#<?php echo $id; ?>', 'input[name="<?php echo $formSlug; ?>"]', '<?php echo $spaceType; ?>');
    });
</script>