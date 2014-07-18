<?php if ($buildFields): ?>


<?php if (!$disableFormOpen): ?>
<?php echo form_open_multipart($formUrl, 'class="streams_form"'); ?>
<?php endif; ?>


<!-- .panel-body -->
<div class="panel-body">

	<?php foreach ($buildFields as $field): ?>

		<div class="form-group
		    streams-field-type-<?php echo $field->field_type; ?>
		    streams-field-<?php echo $field->field_namespace; ?>-<?php echo $field->field_slug; ?>
		    <?php  echo in_array($field->field_slug, $hidden) ? 'hidden' : null;  ?>">
		<div class="row">
			
			<?php echo $field->input_row; ?>

		</div>
		</div>

	<?php endforeach; ?>

</div>
<!-- /.panel-body -->

<?php if (!$new) { ?><input type="hidden" value="<?php echo $entry->id;?>" name="row_edit_id" /><?php } ?>

<?php if (!$disableFormOpen and $buttons): ?>
        <?php include('buttons.php'); ?>
<?php endif; ?>


<?php if (isset($disableFormOpen) and ! $disableFormOpen): echo form_close(); endif; ?>

<?php else: ?>


<div class="alert alert-info m">

<?php

	if (isset($noFieldsMessage) and $noFieldsMessage) {
        echo lang_label($noFieldsMessage);
    } else {
        echo lang('streams:no_fields_msg_first');
    }
?>
</div>

<?php endif; ?>

<script>
    // Might use this later to pass a custom message
    $(function() {

        $('button').click(function() {

            // if have customMessage class, show confirmation
            //alert('this got clicked, but not returning');
            // return false;

        })


    });

</script>
