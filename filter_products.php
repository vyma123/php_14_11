<!-- filter_products.php -->
<?php
require_once 'includes/db.inc.php';
require_once 'includes/functions.php';

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

$allowed_sort_columns = ['id', 'product_name', 'price'];
$sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowed_sort_columns) ? $_GET['sort_by'] : 'id';
$allowed_order_directions = ['ASC', 'DESC'];
$order = isset($_GET['order']) && in_array($_GET['order'], $allowed_order_directions) ? $_GET['order'] : 'ASC';

$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$price_from = $_GET['price_from'] ?? null;
$price_to = $_GET['price_to'] ?? null;

$category = isset($_GET['category']) ? $_GET['category'] : [];  
$tag = isset($_GET['tag']) ? $_GET['tag'] : [];  

// echo implode(", ", $category);
// echo implode(", ", $tag);


$query = "
SELECT products.*, 
    GROUP_CONCAT(DISTINCT p_tags.name_ SEPARATOR ', ') AS tags, 
    GROUP_CONCAT(DISTINCT p_categories.name_ SEPARATOR ', ') AS categories,
    GROUP_CONCAT(DISTINCT g_images.name_ SEPARATOR ', ') AS gallery_images
FROM products
LEFT JOIN product_property pp_tags ON products.id = pp_tags.product_id
LEFT JOIN property p_tags ON pp_tags.property_id = p_tags.id AND p_tags.type_ = 'tag'
LEFT JOIN product_property pp_categories ON products.id = pp_categories.product_id
LEFT JOIN property p_categories ON pp_categories.property_id = p_categories.id AND p_categories.type_ = 'category'
LEFT JOIN product_property pp_gallery ON products.id = pp_gallery.product_id
LEFT JOIN property g_images ON pp_gallery.property_id = g_images.id AND g_images.type_ = 'gallery'
WHERE products.product_name LIKE :search_term
";


if (!empty($category) && $category[0] != 0) {
    $categoryPlaceholders = implode(',', array_map(function ($index) {
        return ':category' . $index;
    }, array_keys($category)));
    $query .= " AND pp_categories.property_id IN ($categoryPlaceholders)";
}

if (!empty($tag) && $tag[0] != 0) {
    $tagPlaceholders = implode(',', array_map(function ($index) {
        return ':tag' . $index;
    }, array_keys($tag)));
    $query .= " AND pp_tags.property_id IN ($tagPlaceholders)";
}
if (!empty($gallery)) {
    $query .= " AND g_images.name_ LIKE :gallery"; 
}

if (!empty($date_from)) {
    $query .= " AND products.date >= :date_from"; 
}

if (!empty($date_to)) {
    $query .= " AND products.date <= :date_to"; 
}

if (!empty($price_from)) {
    $query .= " AND products.price >= :price_from"; 
}

if (!empty($price_to)) {
    $query .= " AND products.price <= :price_to";
}


$query .= " GROUP BY products.id 
            ORDER BY $sort_by $order 
            LIMIT :start_from, :per_page";


$stmt = $pdo->prepare($query);

$searchTermLike = "%$searchTerm%";
$stmt->bindParam(':search_term', $searchTermLike, PDO::PARAM_STR);

if (!empty($category) && $category[0] != 0) {
    foreach ($category as $index => $category_id) {
        $stmt->bindValue(':category' . $index, $category_id, PDO::PARAM_INT);
    }
}

if (!empty($tag) && $tag[0] != 0) {
    foreach ($tag as $index => $tag_id) {
        $stmt->bindValue(':tag' . $index, $tag_id, PDO::PARAM_INT);
    }
}


if (!empty($date_from)) {
    $stmt->bindParam(':date_from', $date_from);
}

if (!empty($date_to)) {
    $stmt->bindParam(':date_to', $date_to);
}

if (!empty($price_from)) {
    $stmt->bindParam(':price_from', $price_from);
}

if (!empty($price_to)) {
    $stmt->bindParam(':price_to', $price_to);
}

$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page_record, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);




if (!empty($category) || !empty($tag) || (!empty($date_from) && !empty($date_to)) || (!empty($price_from) && !empty($price_to))) {
    $total_records = getRecordCount($pdo, $searchTermLike, $category, $tag, $date_from, $date_to, $price_from, $price_to);
} else {
    $count_query = "SELECT COUNT(*) FROM products WHERE product_name LIKE :search_term";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->bindParam(':search_term', $searchTermLike, PDO::PARAM_STR);
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
}




echo "<div id='filter_box_table' class='box_table2'>";
echo "<table id='productTables' class='ui compact celled table'>";
echo "
<thead>
<tr>
  <th class='date'>Date</th>
  <th class='prd_name'>Product name</th>
  <th>SKU</th>
  <th>Price</th>
  <th>Feature Image</th>
  <th class='gallery_name'>Gallery</th>
  <th >Categories</th>
  <th class='tag_name'>Tags</th>
  <th id='action_box' class='action_box'>
                <span>Action</span>
                <div class='box_delete_buttons'>
                    <a  class='delete_buttons' href='#'>
                        <i class='trash icon'></i>
                    </a>
                </div>
            </th>
</tr>
</thead>
";
echo "<tbody>";
if (count($results) > 0) {
    foreach ($results as $row) {
        
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['sku']) . "</td>";
    echo "<td>" . htmlspecialchars($row['price']) . "</td>";
    echo "<td><img height='30' src='./uploads/" . htmlspecialchars($row['featured_image']) . "'></td>";

    $galleryImages = $row['gallery_images'];
    if (!empty($galleryImages)) {
        $galleryImagesArray = explode(', ', $galleryImages);
        echo "<td>
                <div class='gallery-container'>";
        foreach ($galleryImagesArray as $image) {
            echo "<img height='30' src='./uploads/" . htmlspecialchars($image) . "'>";
        }
        echo "
        </div>
        </td>";
    } else {
        echo "<td>No gallery images</td>";
    }
    echo "<td>" . htmlspecialchars($row['categories']) . "</td>";
    echo "<td>" . htmlspecialchars($row['tags']) . "</td>";

    echo "<td>
    <input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>
    <button  type='submit' value='" . htmlspecialchars($row['id']) . "' class='edit_button'>
        <i class='edit icon'></i>
    </button>
     <a class='delete_button' data-id='".$row['id'] ."'>
                <i class='trash icon'></i>
                </a>
    </td>";

    echo "</tr>";
}}else {
    echo "
    <tr>
        <td colspan='9' style='text-align: center;'>Product not found</td>
    </tr>";
}
echo "</tbody>";
echo "</table>";

echo '</div>';

?>


<div id="paginationBox" class="pagination_box fil">
    <div class="ui pagination menu">
        <?php
        $total_pages = ceil($total_records / $per_page_record);

   
        if ($page > 1) {
            echo '<a class="item pagination-link active" data-page="' . ($page - 1) . '">
            <i class="arrow left icon"></i>
            </a>';
        } else {
            echo '<a class="item disabled">
            <i class="arrow left icon"></i>
            </a>';
        }

     
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = ($i == $page) ? 'active' : '';
            echo '<a class="item pagination-link ' . $active_class . '" data-page="' . $i . '">' . $i . '</a>';
        }

      
        if ($page < $total_pages) {
            echo '<a class="item pagination-link" data-page="' . ($page + 1) . '">
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


<script>


//delete one product
// $(document).ready(function() {
//     $('#tableID').on('click', '.delete_button', function(event) {
//         event.preventDefault(); // Ngăn chặn load lại trang

//         const id = $(this).data('id'); // Lấy ID từ thuộc tính data-id

//         if (confirm("Bạn có chắc chắn muốn xóa mục này không?")) { // Xác nhận xóa
//             $.ajax({
//                 url: 'delete_one_product.php', 
//                 type: 'POST',
//                 data: { id: id }, // Gửi ID tới server
//                 success: function(response) {
//                     // Kiểm tra phản hồi từ server
//                     if (response == 'success') {
//                         alert('Đã xóa thành công');
//                         // Ẩn hoặc xóa mục khỏi giao diện mà không tải lại trang
//                         $(`a[data-id='${id}']`).closest('tr').remove();

//                         $('.mytable').load(location.href + " .mytable"); 
//                         $('#paginationBox').load(location.href + " #paginationBox"); 
//                         $('#paginationBoxx').load(location.href + " #paginationBoxx"); 



//                     } else {
//                         alert('Xóa không thành công');
//                     }
//                 },
//                 error: function() {
//                     alert('Có lỗi xảy ra, vui lòng thử lại');
//                 }
//             });
//         }
//     });
// });



function bindHoverEvents() {
    $('#tableID').on('mouseover', function() {
        $('#action_box .box_delete_buttons').css('display', 'block');
    });

    $('#tableID').on('mouseout', function() {
        $('#action_box .box_delete_buttons').css('display', 'none');
    });
}




$(document).ready(function() {
    $('#productTables').on('click', '.delete_buttons', function(event) {
        event.preventDefault();
        
        if (confirm('Xác nhận xóa tất cả!')) {
            $.ajax({
                url: 'delete.php', 
                type: 'POST',
                success: function(response) {
                    $('#productTables').load(location.href + " #productTables"); 
                    $('#paginationBox').load(location.href + " #paginationBox"); 
                },
                error: function(xhr, status, error) {
                    alert('Đã xảy ra lỗi: ' + error);
                }
            });
        }
    });
    });

    $(document).ready(function() {
  $('#productTables').on('mouseenter', function() {
    $('#action_box .box_delete_buttons').show();
  });

  $('#productTables').on('mouseleave', function() {
    $('#action_box .box_delete_buttons').hide();
  });
});


$(document).ready(function () {
    $(document).on('click', '.pagination-link', function (e) {
        e.preventDefault();
        var page = $(this).data('page');
        
        if (page !== <?php echo $page; ?>) {
            loadPage(page);
        }
    });

    function loadPage(page) {

        var categoryString = '<?php echo implode(",", $category); ?>';
    var tagString = '<?php echo implode(",", $tag); ?>';
        $.ajax({
            url: 'filter_products.php',
            type: 'GET',
            data: {
                page: page,
                search: '<?php echo $searchTermLike; ?>',
                sort_by: '<?php echo $sort_by; ?>',
                order: '<?php echo $order; ?>',
                category: categoryString,
                tag: categoryString,
                date_from: '<?php echo $date_from; ?>',
                date_to: '<?php echo $date_to; ?>',
                price_from: '<?php echo $price_from; ?>',
                price_to: '<?php echo $price_to; ?>'
            },
            success: function (response) {
                var tableData = $(response).find('.box_table2').html(); 
                $('.box_table2').html(tableData);

            },
            error: function () {
                alert('An error occurred while loading data.');
            }
        }); 
    }

});
</script>





