<?php
function getAllCarMakes($conn) {
    $sql = "SELECT id, name FROM car_makes WHERE is_active = TRUE ORDER BY name";
    $result = $conn->query($sql);
    $makes = [];
    while ($row = $result->fetch_assoc()) {
        $makes[] = $row;
    }
    return $makes;
}

function getModelsByMake($conn, $make_id) {
    $sql = "SELECT id, name, year_from, year_to, vehicle_type 
            FROM car_models 
            WHERE make_id = ? AND is_active = TRUE 
            ORDER BY name, year_from";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $make_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $models = [];
    while ($row = $result->fetch_assoc()) {
        $models[] = $row;
    }
    return $models;
}

function searchCarModels($conn, $search_term) {
    $sql = "SELECT cm.id, cm.name as model_name, mk.name as make_name, cm.year_from, cm.year_to, cm.vehicle_type
            FROM car_models cm
            JOIN car_makes mk ON cm.make_id = mk.id
            WHERE cm.name LIKE ? AND cm.is_active = TRUE
            ORDER BY mk.name, cm.name
            LIMIT 50";
    $stmt = $conn->prepare($sql);
    $search_term = '%' . $search_term . '%';
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $models = [];
    while ($row = $result->fetch_assoc()) {
        $models[] = $row;
    }
    return $models;
}
?>