<div class="wrap">
    <h2><?php echo $this->title ?></h2>
    <?php if ( !empty( $this->transaction ) ) {
        foreach ($this->transaction as $key => $value) {
            echo "<p>";
                echo $key.' : '.$value;
            echo "</p>";
        }
    } ?>

    <p>
        <a class="button" href="admin.php?page=mercanet_transactions"><?php echo __( 'Back to the transactions', 'mercanet' ); ?></a>
    </p>
</div>
