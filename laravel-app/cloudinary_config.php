<?php
/**
 * Cloudinary設定
 * 画像アップロード用のクラウドストレージ設定
 */

// Cloudinary設定（環境変数対応）
if (!function_exists('getCloudinaryConfig')) {
    function getCloudinaryConfig() {
        return [
            'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'] ?? 'your-cloud-name',
            'api_key' => $_ENV['CLOUDINARY_API_KEY'] ?? 'your-api-key',
            'api_secret' => $_ENV['CLOUDINARY_API_SECRET'] ?? 'your-api-secret',
            'upload_preset' => $_ENV['CLOUDINARY_UPLOAD_PRESET'] ?? 'your-upload-preset'
        ];
    }
}

// 画像アップロード関数
if (!function_exists('uploadImageToCloudinary')) {
    function uploadImageToCloudinary($file_path, $options = []) {
        $config = getCloudinaryConfig();
        
        // Cloudinary APIを使用して画像をアップロード
        $url = "https://api.cloudinary.com/v1_1/{$config['cloud_name']}/image/upload";
        
        $post_data = [
            'file' => new CURLFile($file_path),
            'upload_preset' => $config['upload_preset']
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['secure_url'])) {
            return $result['secure_url'];
        }
        
        return null;
    }
}
?>

