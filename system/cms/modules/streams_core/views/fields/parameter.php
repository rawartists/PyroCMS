<div class="form-group">
    <div class="row">
        <label class="col-lg-2" for="<?php echo $input_slug ?>"><?php echo $input_name ?>
            <?php if( isset($instructions) and $instructions ): ?>
                <br /><small><?php echo $instructions ?></small>
            <?php endif ?>
        </label>
        <div class="col-lg-10">
            <?php echo isset($input) ? $input : null; ?>
        </div>
    </div>
</div>