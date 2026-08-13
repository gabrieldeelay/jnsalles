<?php

function output_pdf_client_list_backend($dompdf)
{
    $dompdf->load_html(template_clients_pdf());
    $dompdf->set_option('defaultFont', 'Times New Roman', 'isHtml5ParserEnabled', true);
    $dompdf->render();
    $strtotime = strtotime('now');
    $name = 'relatorio-de-compra-' . $strtotime;
    $dompdf->stream($name . '.pdf', ['Attachment' => false]);
}

function template_clients_pdf()
{
    ob_start();
    global $conn;
    date_default_timezone_set('America/Sao_Paulo');
    $product_id = $_GET['id'];
    $paid = 0;
    $pending = 0;
    $qty_numbers_pending = 0;
    $qty_numbers_paid = 0;
    $purchase_amount_manual = 0;
    $purchase_amount_mercadopago = 0;
    $purchase_amount_openpix = 0;
    $purchase_amount_paggue = 0;
    $purchase_amount_gerencianet = 0;
    $discount_amount = 0;
    $purchasemp = 0;
    $purchasegn = 0;
    $purchasep = 0;
    $purchaseop = 0;
    $discount_amount_m = 0;
    $qty_numbers_paid_m = 0;
    $discount_amount_mp = 0;
    $qty_numbers_paid_mp = 0;
    $discount_amount_gn = 0;
    $qty_numbers_paid_gn = 0;
    $discount_amount_op = 0;
    $date = date('d/m/Y', time());
    $qty_numbers_paid_op = 0;
    $order_numbers_without_spaces = '';
    $discount_amount_p = 0;
    $qty_numbers_paid_p = 0;
    $total1 = 0;
    $total2 = 0;
    date_default_timezone_set('America/Sao_Paulo');
    $dateshow = date('d/m/Y \\à\\s H:i:s', time());
    $stmt = $conn->prepare("
    SELECT order_list.*, customer_list.firstname, customer_list.lastname 
    FROM `order_list`
    INNER JOIN customer_list ON order_list.customer_id = customer_list.id
    WHERE `order_list`.`id` = ?
");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();

    
    if ($row = $result->fetch_assoc()) {
        $raffle_name = $row['product_name'];
        $customer_name = $row['firstname'] . ' ' . $row['lastname'];
        $payment_method = $row['payment_method'];
        $status = $row['status'];
        $order_numbers = $row['order_numbers'];
        $total_amount = $row['total_amount'];
         $total_amount = number_format($total_amount, 2, ',', '.');
        $discount_amountx = (!empty($row['discount_amount']) ? $row['discount_amount'] : 0);
        $value_per_number = $row['total_amount'] / $row['quantity'];
        $number_of_numbers = $row['quantity'];
        $paid = $row['status'] == 2 ? $row['quantity'] : 0;
        $pending = $row['status'] == 1 ? $row['quantity'] : 0;
        $order_numbers_without_spaces = preg_replace('/\s+/', '', $row['order_numbers']);

        $date = date('d/m/Y', strtotime($row['date_created'])) . ' às ' . date('H:i:s', strtotime($row['date_created']));
    } else {
        // Handle the case where no rows are found
        echo "No record found for product_id = $product_id";
    }
    
    $stmt->close(); // Close the statement when you're done
    

    echo '    ' . "\r\n\r\n\t" . '<!DOCTYPE html>' . "\r\n\t" . '<html lang="pt-BR">' . "\r\n\t" . '<head>' . "\r\n\t\t" . '<meta charset="UTF-8">' . "\r\n\t\t" . '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\r\n\t\t" . '<meta http-equiv="X-UA-Compatible" content="ie=edge">' . "\r\n\t\t" . '<title>RELATÓRIO DE VENDAS | ';
 


    echo $raffle_name . '</title>' . "\r\n\t\t" . 

  '  </head>' .
    "\r\n\t" . 

    
    '<body style="cursor: auto;font-family:Montserrat, sans-serif;margin:0px;font-size:16px;font-weight:400;line-height:24px;color:rgb(33, 37, 41);text-align:left;background-color:rgb(255, 255, 255);box-sizing:border-box;">
        <div style="box-sizing:border-box;">
            <div style="max-width:1140px;width: 100%;padding-right:15px;padding-left:15px;margin-right: auto;margin-left: auto;box-sizing:border-box;">
                <div style="margin-top:8px;border-bottom:1.5px solid rgb(222, 226, 230);background-color:rgb(255, 255, 255);padding:12px 20px;border-top:1.5px solid rgba(0, 0, 0, 0.125);box-sizing:border-box;">
                    <p style="font-family:Montserrat, sans-serif;text-align:center;margin-bottom:0px;margin-top:0px;box-sizing:border-box;">RELATÓRIO DE VENDAS </p>
                    <p style="font-size:22px; font-family:Montserrat, sans-serif;text-align:center;margin-bottom:0px;margin-top:0px;box-sizing:border-box;">'. 
                    $raffle_name . '</p>
                    <p style="font-size:18pxfont-family:Montserrat, sans-serif;text-transform:uppercase;text-align:center;margin-bottom:0px;margin-top:0px;box-sizing:border-box;">
                    
                    </p>
                </div> <br style="box-sizing:border-box;" /><br style="box-sizing:border-box;" />
                <div style="margin-bottom:16px;box-sizing:border-box;">
                 <span style="font-family:Montserrat, sans-serif;box-sizing:border-box;">Cliente: ' . $customer_name  .'</span>
                    <br style="box-sizing:border-box;" /> 
                
                    <span style="font-family:Montserrat, sans-serif;box-sizing:border-box;">Bilhetes Pagos: '.$paid.'</span>
                    <br style="box-sizing:border-box;" /> <span style="font-family:Montserrat, sans-serif;box-sizing:border-box;">Bilhetes Reservados: '.$number_of_numbers.'</span>
                    <br/>                                      
                    <br style="box-sizing:border-box;" /> <span style="display:block; text-align:center; font-family:Montserrat, sans-serif;box-sizing:border-box;">Data: <span style=" font-family:Montserrat, sans-serif;box-sizing:border-box;">'.$date.'</span> </span>


                    
                      <br style="box-sizing:border-box;" /> <span style=" display:block; text-align:center;font-family:Montserrat, sans-serif;box-sizing:border-box;">Total: <span style=" font-family:Montserrat, sans-serif;box-sizing:border-box;">R$ '.$total_amount.'</span></span>

                      
                </div>
                
    

            <div style="width: 98%;margin-inline:1%; word-wrap: break-word; word-break: break-all; overflow-wrap: break-word; overflow: hidden; margin-top: 36px; display: flex; padding: 12px; text-align: left;">' . $order_numbers_without_spaces . '</div>
        </div>        </div>
    </body>
    
    
    </html>';
    return ob_get_clean();
}

require_once '../config.php';
ini_set('memory_limit', '1024M');
require_once '../vendor/autoload.php';
if (isset($_GET['id']) && $_GET['id'] && $_settings->userdata('type')) {
    $dompdf = new Dompdf\Dompdf();
//  output_pdf_client_list_backend($dompdf);
 echo template_clients_pdf();
} else {
    echo 'Você não tem permissão para acessar essa página.';
}

echo "\r\n";
