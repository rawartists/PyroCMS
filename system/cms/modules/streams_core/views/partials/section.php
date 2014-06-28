<div class="p">

    <!-- .panel -->
    <section class="panel panel-default animated-zing fadeIn">

        <div class="panel-heading">
            <h3 class="panel-title">
                <?php if(isset($template['page_title'])) { echo lang_label($template['page_title']); } ?>
            </h3>
            <?php if(isset($description)) { echo '<p class="c-gray">'. $description. '</p>'; } ?>
        </div>

        <?php echo $content; ?>

    </section>
    <!-- /.panel -->

</div>