<!-- index.php -->
<?php
require_once 'includes/db.inc.php';
require_once './includes/functions.php';

$results = select_all_products($pdo);

$searchTerm = isset($_GET['search']) ? test_input($_GET['search']) : '';
$per_page_record = 5;
$page = isset($_GET["page"]) ? $_GET["page"] : 1;
$page = filter_var($page, FILTER_VALIDATE_INT) !== false ? (int)$page : 1;

$start_from = ($page - 1) * $per_page_record;

$query = "SELECT * FROM products LIMIT :start_from, :per_page";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page_record, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);




?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="styles/style.css" type="text/css">

    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
   <style>
    .box_gallery{
    display: flex ;
    align-items: center;
    justify-content: space-between;
    height: 80px !important;
    }

    body::-webkit-scrollbar {
    display: none;

    }
    #galleryPreviewContainer {
    overflow-x: auto; 
    white-space: nowrap; 
}

#galleryPreviewContainer img {
    height: 80px;
    display: inline-block; 
}

    .form_add_products{
        padding: 1rem;
    }

    .img_box{
        overflow: hidden;
        width: 500px;
    }

    .edit_button {
    border: none;
    cursor: pointer;
    border-radius: 5px; 
    background-color: #fff;
}

.box_button_add{
    margin-top: 1rem;
}

#editProductButton{
    
}

#addProductButton{
}

.tag_name{
    width: 190px;
}

.prd_name{
    width: 206px;
}

table thead .date{
    width: 100px;
}

#tableID{
    text-align: center;
}

.select_tag{
    width: 140px  !important;
}
.select_category{
    width: 140px  !important;
}
.box_table{

    height: 400px;
    overflow-y: auto;  
    margin-bottom: 20px; 
}
.box_table2{

height: 400px;
overflow-y: auto;  
margin-bottom: 20px; 
}


.category_boxx{
    width: 115px;
    position: relative;
}

.category_boxx .dropdown{
    position: absolute !important;
    width: 115px;

}


.action_box{
    display: flex;
    align-items: center;
    justify-content: center;
    justify-content: space-around;
}

#action_box .box_delete_buttons {
  display: none; 
  left: 5rem;
  cursor: pointer;
}

.gallery_name{
    width: 200px;
}

.gallery-container {
    white-space: nowrap;   
    overflow-x: auto;      
    max-width: 200px;      
}

.gallery-container img {
    display: inline-block; 
}

#paginationBoxx{
    display: none ;
}

#productTables{
    text-align: center;

}

   </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css"  />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
    

    <title>PHP1</title>
</head>

<body>

<?php include('model_add_product.php');?>
<?php include('model_add_property.php');?>


    <section class="container">
        <h1>PHP1</h1>
        <div class="product_header">
            <div class="product_header_top">
                <div>
                    <button id="add_product" class="ui primary button" >Add product</button>
                    <button id="add_property" class="ui button">Add property</button>
                    <a href="#" class="ui button">Sync online</a>
                </div>
                <div class="ui icon input">
                    <input id="search" type="text"  oninput="loadApplyFilters(event)" placeholder="Search product..." value="">
                </div>
            </div>
            <div class="product_header_bottom">
                <select class="ui dropdown" id="sort_by">
                    <option value="date">Date</option>
                    <option value="product_name">Product name</option>
                    <option value="price">Price</option>
                </select>
                <select class="ui dropdown" id="order">
                    <option value="ASC">ASC</option>
                    <option value="DESC">DESC</option>
                </select>

                <div class="category_boxx">

                <select name="category[]" id="category" class="ui fluid search dropdown select_category" multiple="">
                <option value="">Category</option>
                <?php
                $query = "SELECT p.id, p.name_ FROM property p WHERE p.type_ = 'category'";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $selectedCategory = $_GET['category'] ?? [];
                foreach ($categories as $category) {
                    $selected = in_array($category['id'], $selectedCategory) ? 'selected' : '';
                    echo "<option $selected value=\"{$category['id']}\">" . htmlspecialchars($category['name_']) . "</option>";
                }
                ?>
        </select>
        </div>
        <div class="category_boxx">
        <select name="category[]" id="tag" class="ui fluid search dropdown select_tag" name="tag[]" multiple="">
                <option value="">Select Tag</option>
                <?php
                $query = "SELECT p.id, p.name_ FROM property p WHERE p.type_ = 'tag'";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $selectedTag = $_GET['tag'] ?? [];
                foreach ($tags as $tag) {
                    $selected = in_array($tag['id'], $selectedTag) ? 'selected' : '';
                    echo "<option $selected value=\"{$tag['id']}\">" . htmlspecialchars($tag['name_']) . "</option>";
                }
                ?>
            </select>
            </div>



                <div class="ui input"><input type="date" id="date_from"></div>
                <div class="ui input"><input type="date" id="date_to"></div>
                <div class="ui input"><input  onkeypress="return isNumber(event)" type="number" id="price_from" placeholder="price from"></div>
                <div class="ui input"><input  onkeypress="return isNumber(event)" type="number" id="price_to" placeholder="price to"></div>
                <button id="filter" onclick="applyFilters(event)" class="ui button">Filter</button>
            </div>
        </div>
     
        <!-- table -->
         <div id="box_table2"></div>

         <div id="box_table" class="box_table table_index">

            <table id="tableID" class="ui compact celled table ">
            <thead>
            <tr>
            <th class="date">Date</th>
            <th class="prd_name">Product name</th>
            <th>SKU</th>
            <th>Price</th>
            <th>Feature Image</th>
            <th class="gallery_name">Gallery</th>
            <th >Categories</th>
            <th class="tag_name">Tags</th>
            <th id="action_box" class="action_box">
                <span>Action</span>
                <div class="box_delete_buttons">
                    <a  class="delete_buttons" >
                        <i class="trash icon"></i>
                    </a>
                </div>
            </th>
            </tr>
            </thead>
            <tbody id="productTableBody">
            <?php 
                if (isset($_GET["page"])) {    
                    $page  = $_GET["page"];    
                } else {    
                    $page=1;    
                }    
                
                
                if (count($results) > 0) {
                    foreach ($results as $row){
                    $product_id = $row['id']; ?>


            <tr>
            <td><?php echo htmlspecialchars($row['date'])?></td>
            <td class="product_name"><?php echo htmlspecialchars($row['product_name'])?></td>
            <td class="sku"><?php echo htmlspecialchars($row['sku'])?></td>
            <td><?php echo htmlspecialchars($row['price'])?></td>
            <td>
                <img height="30" src="./uploads/<?php echo $row['featured_image']; ?>">
            </td>
            <td class="gallery_images">
                <div class="gallery-container">

                    <?php 
                    $query = "SELECT p.name_ FROM product_property pp
                            JOIN property p ON pp.property_id = p.id
                            WHERE pp.product_id = :product_id AND p.type_ = 'gallery'";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $galleryImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($galleryImages as $image) {?> 
                <img  height="40" src="./uploads/<?= $image['name_'] ?>">
                <?php }?>
            </div>
            </td>
            <td>
            <?php 
                    $query = "SELECT p.name_ FROM product_property pp
                            JOIN property p ON pp.property_id = p.id
                            WHERE pp.product_id = :product_id AND p.type_ = 'category'";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $totalCategories = count($categories);
                    foreach ($categories as $index => $category) {?> 
                    <span><?php echo htmlspecialchars($category['name_']);
                            if($index < $totalCategories -1 ){
                                echo ', ';
                            }
                        ?></span>
                    <?php }?>
            </td>
            <td>
            <?php 
                    $query = "SELECT p.name_ FROM product_property pp
                            JOIN property p ON pp.property_id = p.id
                            WHERE pp.product_id = :product_id AND p.type_ = 'tag'";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $totalTags = count($tags);
                    foreach ($tags as $index => $tag) {?> 
                    <span><?php echo htmlspecialchars($tag['name_']);
                            if($index < $totalTags -1 ){
                                echo ', ';
                            }
                        ?></span>
                    <?php }?>
            </td>
            <td>
            <input  type="hidden" name="id" id="id">


                <button type="submit"   value="<?= $row['id']?>" class="edit_button" >
                <i class="edit icon"></i>
                </button>
            
                <a  class="delete_button" href="">
                <i class="trash icon"></i>
                </a>
            </td>
            </tr>
            <?php }}else {?>
                <tr>
                    <td colspan="9" style="text-align: center;">Product not found</td>
                </tr>
                <?php }?>
            </tbody>
            </table>
        </div>
         
        

<!-- pagination -->
<div id="paginationBox" class="pagination_box">
    <div class="ui pagination menu">
        <?php
        $query = "SELECT COUNT(*) FROM products";
        $count_stmt = $pdo->prepare($query);
        $count_stmt->execute();
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $per_page_record);

        if ($page > 1) {
            echo '<a class="none_pagination item pagination-link active" data-page="' . ($page - 1) . '">
            <i class="arrow left icon"></i>
            </a>';
        } else {
            echo '<a class="item disabled">
            <i class="arrow left icon"></i>
            </a>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = ($i == $page) ? 'active' : '';
            echo '<a class="none_pagination item pagination-link ' . $active_class . '" data-page="' . $i . '">' . $i . '</a>';
        }

        if ($page < $total_pages) {
            echo '<a class="none_pagination item pagination-link" data-page="' . ($page + 1) . '">
<i class="arrow right icon"></i>

            </a>';
        } else {
            echo '<a class="item disabled">
<i class="arrow right icon"></i>

            </a>';
        }
        ?>
    </div>
</div>

<div id="paginationBoxx" class="pagination_box">
    <div class="ui pagination menu">
        <?php
        $query = "SELECT COUNT(*) FROM products";
        $count_stmt = $pdo->prepare($query);
        $count_stmt->execute();
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $per_page_record);

        if ($page > 1) {
            echo '<a class="none_pagination item pagination-link active" data-page="' . ($page - 1) . '">
            <i class="arrow left icon"></i>
            </a>';
        } else {
            echo '<a class="item disabled">
            <i class="arrow left icon"></i>

            </a>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = ($i == $page) ? 'active' : '';
            echo '<a class="none_pagination item pagination-link ' . $active_class . '" data-page="' . $i . '">' . $i . '</a>';
        }

        if ($page < $total_pages) {
            echo '<a class="none_pagination item pagination-link" data-page="' . ($page + 1) . '">
<i class="arrow right icon"></i>
            </a>';
        } else {
            echo '<a class="item disabled">
<i class="arrow right icon"></i>

            </a>';
        }
        ?>
    </div>
</div>

</section>


<script src="./jquery/my_jquery_functions.js">
</script>


<script>
    



    document.getElementById('addProductButton').addEventListener('click', function() {
    let targetElement = document.getElementById('box_table');
    targetElement.style.display = 'block'; 

    
});


    function bindHoverEvents() {
    $('#tableID').on('mouseover', function() {
        $('#action_box .box_delete_buttons').css('display', 'block');
    });

    $('#tableID').on('mouseout', function() {
        $('#action_box .box_delete_buttons').css('display', 'none');
    });
}
    
$('#tableID').load(location.href + " #tableID", function() {
    console.log('#tableID content loaded');
    
    $('#tableID').on('click', '.delete_buttons', function(event) {
        event.preventDefault();
        
        if (confirm('Xác nhận xóa tất cả!')) {
            $.ajax({
                url: 'delete.php', 
                type: 'POST',
                success: function(response) {
                    $('#tableID').load(location.href + " #tableID", function() {
                        console.log('#tableID content reloaded');
                    });

                    $('#paginationBox').load(location.href + " #paginationBox > *", function() {
                        console.log('Pagination box reloaded');
                    });
                    $('#paginationBoxx').load(location.href + " #paginationBoxx > *", function() {
                        console.log('Pagination box reloaded');
                    });
                },
                
                error: function(xhr, status, error) {
                    alert('Đã xảy ra lỗi: ' + error);
                }
            });
        }
    });
});

bindHoverEvents();

    $('#category').dropdown();
    $('#tag').dropdown();

$(document).on('click', '.none_pagination', function() {
    $('.pagination_box').css({
        'display': 'none'
    });

   
});

$(document).on('click', '.pagination-link', function() {
    $('.table_index').css({
        'display': 'none'
    });
});

$(document).ready(function () {
    $(document).on('click', '.pagination-link', function (e) {
        e.preventDefault();
        var page = $(this).data('page');
        loadPage(page);
    });
   

    $('#applyFilters').click(function() {
        loadPage(1); 
    });

    function loadPage(page) {
        var search = $('#search').val();
        var sort_by = $('#sort_by').val();
        var order = $('#order').val();
        var category = $('#category').val();
        var tag = $('#tag').val();
        var date_from = $('#date_from').val();
        var date_to = $('#date_to').val();
        var price_from = $('#price_from').val();
        var price_to = $('#price_to').val();

        $.ajax({
            url: 'filter_products.php',
            type: 'GET',
            data: {
                page: page,
                search: search,
                sort_by: sort_by,
                order: order,
                category: category,
                tag: tag,
                date_from: date_from,
                date_to: date_to,
                price_from: price_from,
                price_to: price_to
            },
            success: function(response) {
                $('#box_table2').html(response);
            }
        });
    }
});



</script>


</body>
</html>