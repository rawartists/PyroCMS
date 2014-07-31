<label class="col-lg-2 control-label" for="<?php
use Pyro\Module\Pages\Model\Page;

echo $field_type->form_slug; ?>"><?php echo lang_label($field_type->getField()->field_name); ?>

    <?php if ($field_type->getField()->is_required): ?>
        <span class="required">*</span>
    <?php endif; ?>

    <?php if (!empty($field_type->getField()->instructions)): ?>
    <p class="help-block c-gray-light"><?php echo lang_label($field_type->getField()->instructions); ?></p>
    <?php endif; ?>

</label>

<div class="col-lg-10">
    <div class="d-inline-block">
        <?php $page = Page::find(ci()->input->get('parent')); ?>
        <?php echo site_url($page ? $page->uri : null) . ($page ? '/' : null); ?>
    </div>
    <div class="d-inline-block">
        <?php echo $field_type->getInput(); ?>
    </div>
</div>