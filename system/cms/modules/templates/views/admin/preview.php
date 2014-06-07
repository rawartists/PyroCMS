<div class="modal-dialog" style="width: 80%;">



    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Email Preview</h4>
        </div>

        <div class="modal-body">



        <h4 class="template-subject">
        <?php echo $email_template->subject ?>
    </h4>

    <div class="template-body">
        <?php echo $email_template->body ?>
    </div>


        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">
                <?php echo lang('buttons:close'); ?>
            </button>
        </div>

    </div>

</div>
