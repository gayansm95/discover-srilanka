<?php
require_once('vendor/autoload.php'); // TCPDF library

use TCPDF\TCPDF;

// Receive JSON
$data = json_decode(file_get_contents('php://input'), true);

// Generate PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica','',12);
$pdf->Write(0,"Tour Booking Confirmation\n\n");
$pdf->Write(0,"Name: ".$data['name']."\nEmail: ".$data['email']."\nDate: ".$data['date']."\nTravelers: ".$data['travelers']."\n\nItinerary:\n");
foreach($data['itinerary'] as $i => $place){
    $hotel = $data['hotels'][$i] ?? 'N/A';
    $pdf->Write(0, "Day ".($i+1).": $place - Hotel: $hotel\n");
}
$pdfFile = 'pdf/booking_'.time().'.pdf';
$pdf->Output($pdfFile,'F'); 

// Send Email
$to = $data['email'];
$subject = "Your Tour Booking Confirmation";
$message = "Dear ".$data['name'].",\n\nYour tour booking has been confirmed. Please find attached your itinerary PDF.\n\nAyubowan Tours";
$headers = "From: info@ayubowantours.com\r\n";
$separator = md5(time());
$eol = PHP_EOL;

$headers .= "MIME-Version: 1.0".$eol;
$headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"".$eol.$eol;

$body = "--".$separator.$eol;
$body .= "Content-Type: text/plain; charset=\"iso-8859-1\"".$eol;
$body .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
$body .= $message.$eol.$eol;

// Attach PDF
$attachment = chunk_split(base64_encode(file_get_contents($pdfFile)));
$body .= "--".$separator.$eol;
$body .= "Content-Type: application/pdf; name=\"booking.pdf\"".$eol; 
$body .= "Content-Transfer-Encoding: base64".$eol;
$body .= "Content-Disposition: attachment".$eol.$eol;
$body .= $attachment.$eol.$eol;
$body .= "--".$separator."--";

mail($to,$subject,"",$headers);

// Response
echo json_encode(["status"=>"success","message"=>"Booking confirmed! PDF sent to email."]);
?>
