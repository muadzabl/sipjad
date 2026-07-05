<?php
// Koneksi ke Firebase Realtime Database
$firebaseURL = "https://gen-lang-client-0031322642-default-rtdb.asia-southeast1.firebasedatabase.app/";
$firebaseAPIKey = "AIzaSyAKm400EiAYtZgAIcLc4RU3RCCKuA7P2D4"; // Anda juga dapat memasang ?auth=$firebaseAPIKey pada query string jika rules database membutuhkan autentikasi API Key (meski RDB biasanya pakai OAuth/IdToken, atau dibiarkan true). Di sini API key disertakan sebagai parameter query jika diperlukan, tapi dalam banyak kasus PHP langsung tembak .json

function getFirebaseUrl($path) {
    global $firebaseURL, $firebaseAPIKey;
    // Tambahkan .json agar Realtime Database merespons sebagai JSON.
    // Jika perlu auth bisa ditambahkan ?auth= atau parameter lain
    $url = $firebaseURL . ltrim($path, '/') . '.json';
    // Contoh jika butuh params: $url .= "?key=" . $firebaseAPIKey;
    return $url;
}

// Helper GET
function firebase_get($path) {
    $url = getFirebaseUrl($path);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    return null;
}

// Helper PUSH (Tambah data baru dengan ID unik buatan Firebase)
function firebase_push($path, $data) {
    $url = getFirebaseUrl($path);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true); // Biasanya mereturn array( 'name' => '-Nxxxxxxx' )
    }
    return false;
}

// Helper PUT (Overwrite semua data di node tertentu atau Set data dengan key tertentu)
function firebase_put($path, $data) {
    $url = getFirebaseUrl($path);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    return false;
}

// Helper PATCH (Update field tertentu tanpa overwriting seluruh node)
function firebase_patch($path, $data) {
    $url = getFirebaseUrl($path);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    return false;
}

// Helper DELETE
function firebase_delete($path) {
    $url = getFirebaseUrl($path);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }
    return false;
}
?>
