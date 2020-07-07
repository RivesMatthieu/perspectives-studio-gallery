<?php

class Mercanet_Schedule {

    public static function save($data, $raw_data, $transaction_type, $transaction_id) {
        global $wpdb;

        $amounts_list = preg_split('@;@', $data['instalmentAmountsList'], null, PREG_SPLIT_NO_EMPTY);
        $dates_list = preg_split('@;@', $data['instalmentDatesList'], null, PREG_SPLIT_NO_EMPTY);
        $transactions_reference_list = preg_split('@;@', $data['instalmentTransactionReferencesList'], null, PREG_SPLIT_NO_EMPTY);

        for ($i = 0; $i <= ( (int) $data['instalmentNumber'] - 1 ); $i++) {
            if (!self::is_already_registered($transactions_reference_list[$i])) {

                $order_id = $data['orderId'];
                $mercanet_transaction_id = ( $i == 0 ) ? $transaction_id : null;
                $transaction_reference = $transactions_reference_list[$i];
                $masked_pan = ( $i == 0 ) ? $data['maskedPan'] : null;
                $amount = (float) $amounts_list[$i] / 100;
                $date_add = $data['transactionDateTime'];
                $date_capture = ( $i == 0 ) ? $data['transactionDateTime'] : null;
                $date_tmp = DateTime::createFromFormat('ymd', $dates_list[$i]);
                $date_to_capture = $date_tmp->format('Y-m-d');
                $captured = ( $i == 0 && $data['responseCode'] == '00' ) ? true : false;
                $status = ( $i == 0 && $data['responseCode'] == '00' ) ? 'Captured' : 'Waiting';

                $wpdb->insert("{$wpdb->prefix}mercanet_schedule", array(
                    'order_id' => $order_id,
                    'mercanet_transaction_id' => $mercanet_transaction_id,
                    'transaction_reference' => $transaction_reference,
                    'masked_pan' => $masked_pan,
                    'amount' => $amount,
                    'date_add' => $date_add,
                    'date_to_capture' => $date_to_capture,
                    'date_capture' => $date_capture,
                    'captured' => $captured,
                    'status' => $status,
                        )
                );
            }
        }
    }

    public static function is_already_registered($reference) {
        if (empty($reference)) {
            return false;
        }

        global $wpdb;
        $result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}mercanet_schedule
                               WHERE transaction_reference = '$reference'");

        if (!empty($result)) {
            return true;
        }

        return false;
    }

    public static function output($order = null) {
        global $post;

        $id = ( $order != null ) ? $order->ID : $post->ID;
        $schedules = self::get_by_order_id($id);
        if (!empty($schedules)) {
            ?>
            <table class="wp-list-table widefat fixed">
                <thead>
                    <tr>
                        <th scope="col"> <?php echo __('Payment brand', 'mercanet') ?></th>
                        <th>Date</th>
                        <th scope="col"> <?php echo __('N° Order', 'mercanet') ?></th>
                        <th scope="col"> <?php echo __('N° Transaction', 'mercanet') ?></th>
                        <th scope="col"> <?php echo __('N° Card', 'mercanet') ?></th>
                        <th scope="col"> <?php echo __('Amount', 'mercanet') ?></th>
                        <th scope="col"> <?php echo __('Previsional date', 'mercanet') ?></th>
                        <th scope="col"> <?php echo __('Capture date', 'mercanet') ?></th>
                        <th scope="col"> <?php echo __('Status', 'mercanet') ?></th>

                    </tr>
                </thead>
                <tbody>
            <?php foreach ($schedules as $schedule) { ?>
                        <tr id="<?php echo $schedule->mercanet_schedule_id; ?>">
                            <td>
                                <?php if (!empty($schedule->payment_mean_brand)) { ?>
                                    <img alt="<?php echo $schedule->payment_mean_brand; ?>" src="<?php echo WP_PLUGIN_URL . "/" . plugin_basename(dirname(__DIR__)) . '/assets/img/' . $schedule->payment_mean_brand . '.png'; ?>" heigth="32px" width="32px">
                <?php } ?>
                            </td>
                            <td><?php echo date_i18n(get_option('date_format') . ' - ' . get_option('time_format'), strtotime($schedule->date_add)); ?></td>
                            <td><?php echo $schedule->order_id; ?></td>
                            <td><?php echo ( $schedule->transaction_reference == 0 ) ? '' : $schedule->transaction_reference; ?></td>
                            <td><?php echo ( $schedule->masked_pan == 0 ) ? '' : $schedule->masked_pan; ?></td>
                            <td><?php echo $schedule->amount; ?></td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($schedule->date_to_capture)); ?></td>
                            <?php if (strtotime($schedule->date_capture) != false) { ?>
                                <td><?php echo date_i18n(get_option('date_format') . ' - ' . get_option('time_format'), strtotime($schedule->date_capture)); ?></td>
                            <?php } else { ?>
                                <td></td>
                <?php } ?>
                            <td><?php echo $schedule->status; ?></td>
                        </tr>
            <?php } ?>
                </tbody>
            </table>
        <?php
        } else {
            echo '<p>' . __('No schedules for this order.', 'mercanet') . '<p>';
        }
    }

    public static function update_schedule($id, $col, $value) {
        global $wpdb;

        $wpdb->update("{$wpdb->prefix}mercanet_schedule", array(
            $col => $value
                ), array(
            'mercanet_schedule_id' => $id
                )
        );
    }

    public static function get_by_order_id($order_id) {
        global $wpdb;
        return $wpdb->get_results("SELECT s.*, payment_mean_brand
                                    FROM {$wpdb->prefix}mercanet_schedule s
                                    LEFT JOIN {$wpdb->prefix}mercanet_transaction t ON s.mercanet_transaction_id = t.transaction_id
                                    WHERE s.order_id = '$order_id'
                                    ORDER BY mercanet_schedule_id ASC");
    }

}