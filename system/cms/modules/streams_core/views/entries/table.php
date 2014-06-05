<?php ci()->benchmark->mark('streams_core_entries_table_view_start');
//$buttons = true;
?>
<?php if ($showFilters and !$disableFilters): ?>

    <?php ci()->benchmark->mark('streams_core_entries_table_view_filters_start'); ?>

    <?php if (!empty($filters)): ?>
        <?php $this->load->view('streams_core/entries/filters'); ?>
    <?php endif; ?>

    <?php ci()->benchmark->mark('streams_core_entries_table_view_filters_end'); ?>

<?php endif; ?>

<section class="table-responsive">
<table class="<?php echo $tableClass; ?>">
<?php ci()->benchmark->mark('streams_core_entries_table_view_column_headers_start'); ?>
<?php if ($showColumnHeaders): ?>
    <thead>
    <tr>
        <?php if ($stream->sorting == 'custom'): ?>
            <th></th><?php endif; ?>
        <?php foreach ($fieldNames as $fieldSlug => $fieldName): ?>
            <?php

            $class = isset($fields[$fieldSlug]['class']) ? $fields[$fieldSlug]['class'] : null;

            // Replace relation: from Cp voodoo
            $fieldSlug = str_replace('relation:', '', $fieldSlug);

            // Use the given slug if any
            $fieldSlug = isset($fields[$fieldSlug]['slug']) ? $fields[$fieldSlug]['slug'] : $fieldSlug;

            // Get our query string
            $query_string = array();

            // Parse it into above array
            parse_str($_SERVER['QUERY_STRING'], $query_string);

            $original_query_string = $query_string;

            // Set the order slug
            $query_string['order-' . $stream->stream_namespace . '-' . $stream->stream_slug] = $fieldSlug;

            // Set the sort string
            $query_string['sort-' . $stream->stream_namespace . '-' . $stream->stream_slug] =
                isset($query_string['sort-' . $stream->stream_namespace . '-' . $stream->stream_slug])
                    ? ($query_string['sort-' . $stream->stream_namespace . '-' . $stream->stream_slug] == 'ASC'
                    ? 'DESC'
                    : 'ASC')
                    : 'ASC';

            // Determine our caret for this item
            $caret = false;

            if (isset($original_query_string['order-' . $stream->stream_namespace . '-' . $stream->stream_slug]) and $original_query_string['order-' . $stream->stream_namespace . '-' . $stream->stream_slug] == $fieldSlug) {
                if (isset($original_query_string['sort-' . $stream->stream_namespace . '-' . $stream->stream_slug])) {
                    if ($original_query_string['sort-' . $stream->stream_namespace . '-' . $stream->stream_slug] == 'ASC') {
                        $caret = '<span class="fa fa-caret-up"></span>';
                    } else {
                        $caret = '<span class="fa fa-caret-down"></span>';
                    }
                } else {
                    $caret = '<span class="fa fa-caret-up"></span>';
                }
            }

            ?>
            <th class="<?php echo $class; ?>">
                <?php if ($enableSortableHeaders): ?>
                    <a href="<?php echo site_url(uri_string()) . '?' . http_build_query($query_string); ?>">
                        <?php echo $fieldName; ?>
                        <?php if ($caret) {
                            echo $caret;
                        } ?>
                    </a>
                <?php else: ?>
                    <?php echo $fieldName; ?>
                <?php endif; ?>
            </th>

        <?php endforeach; ?>
        <?php if ($buttons): ?>

            <?php
            $buttonsClass = (isset($buttonsClass)) ? $buttonsClass : '';
            ?>

            <th class="<?php echo $buttonsClass; ?>"></th>
        <?php endif; ?>
    </tr>
    </thead>
<?php endif; ?>
<?php ci()->benchmark->mark('streams_core_entries_table_view_column_headers_end'); ?>
<tbody>
<?php if ($entries->count() > 0): $i = 0; ?>
    <?php foreach ($entries as $entry) {

        $entry_key = md5($entry.uri_string());

        if (ci()->cache->isEnabled()) {
            if ($cached_entry = ci()->cache->get($entry_key)) {
                echo $cached_entry;
                continue;
            }
        }

        ?>

        <?php
        $i++;
        ci()->benchmark->mark('streams_core_entries_table_view_entry_' . $i . '_start');

        ?>

        <?php ci()->benchmark->mark('streams_core_entries_table_view_parse_entry_' . $i . '_start'); ?>
        <?php $rowClass = ci()->parser->parse_string(
            $tableRowClass,
            $entry,
            true,
            false,
            false,
            false
        ); ?>
        <?php ci()->benchmark->mark('streams_core_entries_table_view_parse_entry_' . $i . '_end'); ?>

        <?php

        // Start the row
        $row_html = '<tr class="' . $rowClass . '">';

        // Sorting ability
        if ($stream->sorting == 'custom'):

            $row_html .= '<td width="30" class="handle">';
            $row_html .= Asset::img(
                "icons/drag_handle.gif",
                "Drag Handle"
            );
            $row_html .= '</td>';

        endif;

        // Each field (column)
        if (!empty($viewOptions)):

            // Iterate
            foreach ($viewOptions as &$viewOption):

                $class = isset($fields[$viewOption]['class']) ? $fields[$viewOption]['class'] : null;
                $row_html .= '<td class="' . $class . '">';
                $row_html .= '<input type="hidden" name="action_to[]" value="' . $entry->getKey() . '"/>';

                ci()->benchmark->mark(
                    'streams_core_entries_table_view_last_viewOption_' . $viewOption . '_start'
                );

                $row_html .= $entry->{$viewOption};

                $row_html .=

                    ci()->benchmark->mark(
                        'streams_core_entries_table_view_last_viewOption_' . $viewOption . '_end'
                    );

                $row_html .= '</td>';

            endforeach; // viewOptions

        endif;


        if ($buttons):
            ci()->benchmark->mark('streams_core_entries_table_view_buttons_start');



            $row_html .= '<td class="text-right">';

            if (isset($buttons)) {
                $all_buttons = array();

                foreach ($buttons as $button) {

                    // Html?
                    if (isset($button['html'])) {
                        $all_buttons[] = ci()->parser->parse_string(
                            $button['html'],
                            $entry,
                            true,
                            true
                        );
                        continue;
                    }

                    // The second is kept for backwards compatibility
                    $url = ci()->parser->parse_string(
                        $button['url'],
                        $entry,
                        true,
                        true
                    );
                    $url = str_replace('-entry_id-', $entry->getKey(), $url);

                    // Label
                    $label = lang_label($button['label']);

                    // Remove URL
                    unset($button['url'], $button['label']);

                    // Parse variables in attributes
                    foreach ($button as $key => &$value) {
                        $value = ci()->parser->parse_string($value, get_object_vars($entry), true);
                    }

                    $all_buttons[] = anchor($url, $label, $button);
                }

                $row_html .= implode('&nbsp;', $all_buttons);
                unset($all_buttons);
            }


            $row_html .= '</td>';

            ?>
            <?php ci()->benchmark->mark('streams_core_entries_table_view_buttons_end'); ?>
        <?php endif; ?>
        <?php $row_html .= '</tr>'; ?>

        <?php

        if (ci()->cache->isEnabled()) {
            ci()->cache->put($entry_key, $row_html, 600);
        }

        echo $row_html;

        ?>

        <?php ci()->benchmark->mark('streams_core_entries_table_view_entry_' . $i . '_end'); ?>
    <?php } ?>
<?php else: ?>
    <tr>
        <td colspan="<?php echo count($fieldNames) + (empty($buttons) ? 0 : 1); ?>">
            <div class="alert alert-info m">
                <?php

                if (isset($no_entries_message) and $no_entries_message) {
                    echo lang_label($no_entries_message);
                } else {
                    echo lang('streams:no_entries');
                }

                ?>
            </div>
            <!--.no_data-->
        </td>
    </tr>
<?php endif; ?>
</tbody>
</table>
</section>

<?php if ($showFooter): ?>
    <?php ci()->benchmark->mark('streams_core_entries_table_view_footer_start'); ?>
    <div class="panel-footer">

        <?php if (isset($pagination) and $pagination): ?>
            <?php if ($showPagination): ?>
                <?php echo $pagination['links']; ?>
            <?php endif; ?>

            <?php if ($showLimitDropdown): ?>
                <?php echo form_dropdown(
                    null,
                    array(5 => 5, 10 => 10, 25 => 25, 50 => 50, 100 => 100),
                    isset($appliedFilters['limit-' . $stream->stream_namespace . '-' . $stream->stream_slug]) ? $appliedFilters['limit-' . $stream->stream_namespace . '-' . $stream->stream_slug] : Settings::get(
                        'records_per_page'
                    ),
                    'class="pull-right" style="width: 100px;" onchange="$(\'select#limit-' . $stream->stream_namespace . '-' . $stream->stream_slug . '\').val($(this).val()).closest(\'form\').find(\'button.btn-success\').click();"'
                ); ?>
            <?php endif; ?>
        <?php endif; ?>
        <div class="clearfix"></div>
    </div>

    <?php if (isset($pagination) and $showResultsCount): ?>
        <div class="stats" style="margin-bottom: -45px;">
            <small class="c-gray m-l" style="line-height: 40px;">
                Showing
                results <?php echo ($pagination['offset'] + 1) . ' - ' . ($pagination['current_page'] * $pagination['per_page']) . ' of ' . $pagination['total']; ?>
            </small>
        </div>
    <?php endif; ?>
    <?php ci()->benchmark->mark('streams_core_entries_table_view_footer_end'); ?>
<?php endif; ?>
<?php ci()->benchmark->mark('streams_core_entries_table_view_end');
?>
