require_once __DIR__ . '/vendor/phpqrcode/qrlib.php';

function generateQRCode($bagId) {
    $url = "https://event.outdoorsecours.fr/sacs/bag_tracking.php?bag_id=" . $bagId;
    $qrCodePath = 'uploads/qrcodes/bag_' . $bagId . '.png';

    if (!is_dir('uploads/qrcodes')) {
        mkdir('uploads/qrcodes', 0777, true);
    }

    QRcode::png($url, $qrCodePath, QR_ECLEVEL_L, 10);
    return $qrCodePath;
}
