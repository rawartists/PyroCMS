<?php echo form_open(uri_string()); ?>
<div class="panel-content">
    <div class="panel-body">

<div class="form_inputs">

    <div class="form-group">
        <div class="row">
            <label class="col-lg-2" for="field_name"><?php echo lang('streams:label.field_name'); ?>
                <span>*</span></label>

            <div class="col-lg-10">
                <?php

                if ($currentField->isFieldNameLang()) {
                    echo '<p><em>' . $currentField->field_name . '</em></p>';
                    echo form_hidden('field_name', $currentField->field_name);
                } else {
                    echo form_input(
                        'field_name',
                        $currentField->field_name,
                        'maxlength="60" id="field_name" autocomplete="off"'
                    );
                }

                ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <label class="col-lg-2" for="field_slug"><?php echo lang('streams:label.field_slug'); ?> <span>*</span><br/>
                <small><?php echo lang('global:slug_instructions'); ?></small>
            </label>

            <div class="col-lg-10">
                <?php echo form_input('field_slug', $currentField->field_slug, 'maxlength="60" id="field_slug"'); ?>
            </div>
        </div>
    </div>

    <?php if (isset($stream)): ?>

        <div class="form-group">
            <div class="row">
                <label class="col-lg-2" for="required"><?php echo lang('streams:label.field_required'); ?></label>

                <div class="col-lg-10">
                    <?php echo form_checkbox(
                        'required',
                        'yes',
                        isset($assignment) ? $assignment->required : false,
                        'id="required"'
                    ); ?>
                </div>
            </div>
        </div>


        <div class="form-group">
            <div class="row">
                <label class="col-lg-2" for="unique"><?php echo lang('streams:label.field_unique'); ?></label>

                <div class="col-lg-10">
                    <?php echo form_checkbox(
                        'unique',
                        'yes',
                        isset($assignment) ? $assignment->unique : false,
                        'id="unique"'
                    ); ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label class="col-lg-2" for="field_instructions"><?php echo lang('streams:label.field_instructions'); ?>
                    <br/>
                    <small><?php echo lang('streams:instr.field_instructions'); ?></small>
                </label>
                <div class="col-lg-10">
                    <?php echo form_textarea(
                        'instructions',
                        isset($assignment) ? $assignment->instructions : null,
                        'id="field_instructions"'
                    ); ?>
                </div>
            </div>
        </div>

        <?php if ($allow_title_column_set): ?>
            <div class="form-group">
                <div class="row">
                    <label class="col-lg-2" for="title_column">
                        <?php echo lang(
                            'streams:label.make_field_title_column'
                        ); ?>
                    </label>

                    <div class="col-lg-10">
                        <?php echo form_checkbox(
                            'title_column',
                            'yes',
                            $title_column_status,
                            'id="title_column"'
                        ); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <?php

    // We send some special params in an edit situation
    $ajax_url = 'streams/ajax/build_parameters';

    if ($this->uri->segment(4) == 'edit'):

        $ajax_url .= '/edit/' . $currentField->id;

    endif;

    ?>

    <div class="form-group">
        <div class="row">
            <label class="col-lg-2" for="field_type"><?php echo lang('streams:label.field_type'); ?>
                <span>*</span></label>

            <div class="col-lg-10">
                <?php echo form_dropdown(
                    'field_type',
                    $field_types,
                    $currentField->field_type,
                    'data-placeholder="' . lang('streams:choose_a_field_type') . '" id="field_type"'
                ); ?>
            </div>
        </div>
    </div>

    <div id="parameters">

        <?php echo $parameters; ?>

    </div>




    </div>
</div>
<div class="panel-footer">

    <button type="submit" name="btnAction" value="save" class="btn btn-sm btn-success"><span>
            <?php echo lang(
                'buttons:save'
            ); ?></span>
    </button>

    <?php if ($uriCancel): ?>
        <a href="<?php echo site_url($uriCancel); ?>" class="btn btn-sm btn-default cancel"><?php echo lang(
                'buttons:cancel'
            ); ?></a>
    <?php endif; ?>
</div>

<?php echo form_close(); ?>