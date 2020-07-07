<div class="wrap">
    <h2><?php echo $this->title ?></h2>
    <?php if ( ! empty( $this->transactions ) ) { ?>
        <table class="wp-list-table widefat fixed">
            <thead>
                <tr>
                    <th>Date</th>
                    <th scope="col"> <?php echo __( 'Payment brand', 'mercanet' ) ?></th>
                    <th scope="col"> <?php echo __( 'N° Order', 'mercanet' ) ?></th>
                    <th scope="col"> <?php echo __( 'N° Authorization', 'mercanet' ) ?></th>
                    <th scope="col"> <?php echo __( 'N° Transaction', 'mercanet' ) ?></th>
                    <th scope="col"> <?php echo __( 'N° Card', 'mercanet' ) ?></th>
                    <th scope="col"> <?php echo __( 'Amount', 'mercanet' ) ?></th>
                    <th scope="col"> <?php echo __( 'Operation type', 'mercanet' ) ?></th>
                    <th scope="col"> <?php echo __( 'Message response', 'mercanet' ) ?></th>
                    <th scope="col"> <?php echo __( 'Acquirer response', 'mercanet' ) ?></th>
                    <th scope="col"> <?php echo __( 'Error message', 'mercanet' ) ?></th>
                    <th scope="col"> <?php echo __( 'Raw data', 'mercanet' ) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach( $this->transactions as $transact ) { ?>
                <tr id="<?php echo $transact->transaction_id ?>">
                    <td><?php echo date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), strtotime( $transact->transaction_date ) ); ?></td>
                    <td>
                        <?php if ( ! empty( $transact->payment_mean_brand ) ) { ?>
                            <img alt="<?php echo $transact->payment_mean_brand; ?>" src="<?php echo WP_PLUGIN_URL . "/" . plugin_basename( dirname( __DIR__ ) ) . '/assets/img/' . $transact->payment_mean_brand . '.png'; ?>" heigth="32px" width="32px">
                        <?php } ?>
                    </td>
                    <td><?php echo $transact->order_id; ?></td>
                    <td><?php echo $transact->authorization_id; ?></td>
                    <td><?php echo $transact->transaction_reference; ?></td>
                    <td><?php echo $transact->masked_pan; ?></td>
                    <td><?php echo $transact->amount; ?></td>
                    <td><?php echo $transact->transaction_type; ?></td>
                    <td><?php echo $transact->response_code_message; ?></td>
                    <td><?php echo $transact->acquirer_response_code_message; ?></td>
                    <td><?php echo $transact->complementary_code_message; ?></td>
                    <td><a href="admin.php?page=mercanet_transactions&transaction=<?php echo $transact->transaction_id ?>">Détails</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <p align="center">Page :
        <?php for ( $i = 1; $i <= $this->nb_pages; $i++ ) {
             if ( $i == $this->current_page ) {
                 echo '[' . $i . ']';
             }
             else {
                echo '<a href="admin.php?page=mercanet_transactions&paged=' . $i . '">' . $i . '</a> ';
             }
        }
        echo '</p>';
    } else {
        echo '<p>' . __( 'No transactions.', 'mercanet' ) . '<p>';
    } ?>
</div>
