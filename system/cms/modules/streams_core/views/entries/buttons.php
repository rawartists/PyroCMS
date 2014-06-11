<?php
/**
 * Created by PhpStorm.
 * User: squareminc
 * Date: 6/11/14
 * Time: 3:27 PM
 */
?>

<div class="panel-footer">
    <button type="submit" name="btnAction" value="save" class="btn btn-success <?php echo (array_key_exists(
        'class',
        $saveButtonMeta
    )) ? $saveButtonMeta['class'] : ''; ?>"><?php echo (array_key_exists(
            'name',
            $saveButtonMeta
        )) ? $saveButtonMeta['name'] : lang('buttons:save'); ?></button>

    <?php if (!empty($redirectExit)): ?>
        <button type="submit" name="btnAction" value="exit" class="btn btn-info <?php echo (array_key_exists(
            'class',
            $saveExitButtonMeta
        )) ? $saveExitButtonMeta['class'] : ''; ?>"><?php echo (array_key_exists(
                'name',
                $saveExitButtonMeta
            )) ? $saveExitButtonMeta['name'] : lang('buttons:save_exit'); ?></button>
    <?php endif; ?>

    <?php if (!empty($redirectCreate)): ?>
        <button type="submit" name="btnAction" value="create" class="btn btn-info <?php echo (array_key_exists(
            'class',
            $saveCreateButtonMeta
        )) ? $saveCreateButtonMeta['class'] : ''; ?>"><?php echo (array_key_exists(
                'name',
                $saveCreateButtonMeta
            )) ? $saveCreateButtonMeta['name'] : lang('buttons:save_create'); ?></button>
    <?php endif; ?>

    <?php if (!empty($redirectContinue)): ?>
        <button type="submit" name="btnAction" value="continue" class="btn btn-info <?php echo (array_key_exists(
            'class',
            $saveContinueButtonMeta
        )) ? $saveContinueButtonMeta['class'] : ''; ?>"><?php echo (array_key_exists(
                'name',
                $saveContinueButtonMeta
            )) ? $saveContinueButtonMeta['name'] : lang('buttons:save_continue'); ?></button>
    <?php endif; ?>

    <a href="<?php //echo site_url(isset($uriCancel) ? ci()->parser->parse_string($uriCancel, array('id' => $entry->id), true) : $redirectSave);
    echo site_url(isset($uriCancel) ? $uriCancel : $redirectSave); ?>"
       class="btn btn-default <?php echo (array_key_exists(
           'class',
           $cancelButtonMeta
       )) ? $cancelButtonMeta['class'] : ''; ?>"><?php echo (array_key_exists(
            'name',
            $cancelButtonMeta
        )) ? $cancelButtonMeta['name'] : lang('buttons:cancel'); ?></a>
</div>