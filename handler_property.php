<?php 
require_once 'includes/db.inc.php';
require_once './includes/functions.php';

if (isset($_POST['save_property'])) {

    $category = test_input($_POST['category']);
    $tag = test_input($_POST['tag']);


    if(empty($category) && empty($tag)){
        $res = [
            'status' => 422,
            'message' => ' At least one field is required.'
        ];
        echo json_encode($res);
        return;
     }


$errors = [];

if(!isValidInput($category) && !empty($category)){
    $errors[] = [
        'field' => 'category',
        'message' => 'just allow special character'
    ];
}

if(!isValidInput($tag) && !empty($tag)){
    $errors[] = [
        'field' => 'tag',
        'message' => 'just allow special character'
    ];
}

if(!empty($errors)){
    $res = [
        'status' => '400',
        'errors' => $errors
    ];
    echo json_encode($res);
    return;
}

     $categories = array_map('trim', explode(',', $category));
        $type_ = 'category';
          
        $errors = [];
        $duplicates = [];
        $successCount = 0;

        foreach($categories as $name_){
            if(!empty($name_)){
                if(check_duplicate($pdo, $type_, $name_)){
                    $duplicates[] = $name_;
                }else{
                    $inserted = insert_property($pdo, $type_, $name_);
                    if($inserted){
                        $successCount++;
                    }else{
                        $errors[] = $name_;
                    }
                }
            }
        }
   
        


        $type_ = 'tag';
        $tags = array_map('trim', explode(',', $tag));
          
        $errors = [];
        $duplicates = [];
        $successCount = 0;

        foreach($tags as $name_){
            if(!empty($name_)){
                if(check_duplicate($pdo, $type_, $name_)){
                    $duplicates[] = $name_;
                }else{
                    $inserted = insert_property($pdo, $type_, $name_);
                    if($inserted){
                        $successCount++;
                    }else{
                        $errors[] = $name_;
                    }
                }
            }
        }

        $message = "$successCount added successfully .";

    if (!empty($duplicates)) {
        $duplicateNames = implode(', ', $duplicates);
        $message .= " exists: $duplicateNames.";
    }



    $res = [
        'status' => 200,
        'message' => $message
    ];

    echo json_encode($res);
    return;
}
?>