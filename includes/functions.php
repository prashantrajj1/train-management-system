<?php
/**
 * Calculates train fare based on distance, train type, and class.
 * 
 * @param string $trainType The type of train (e.g., Rajdhani, Superfast)
 * @param int $distanceKm The distance in kilometers (or estimated)
 * @param string $classCode The class code (e.g., 1A, 2A, SL, Gen)
 * @return int The calculated fare
 */
function calculateFare($trainType, $distanceKm, $classCode) {
    $basePricePerKm = 1.25;
    $fare = 50 + ($distanceKm * $basePricePerKm);
    
    // Train Type Multipliers
    $typeMultipliers = [
        'Rajdhani' => 2.5,
        'Shatabdi' => 2.2,
        'Duronto' => 2.2,
        'Vande Bharat' => 2.8,
        'Superfast' => 1.5,
        'Express' => 1.2,
        'Passenger' => 1.0
    ];
    $typeFactor = $typeMultipliers[$trainType] ?? 1.0;
    
    // Class Multipliers
    $classMultipliers = [
        '1A' => 4.0,
        '2A' => 2.5,
        '3A' => 1.8, // 3-Tier AC
        'SL' => 1.0, // Sleeper
        'Gen' => 0.6, // General
        'General' => 0.6
    ];
    $classFactor = $classMultipliers[$classCode] ?? 1.0;
    
    return round($fare * $typeFactor * $classFactor);
}
?>
