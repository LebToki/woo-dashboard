<?php
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

$woocommerce = new Client(
    'https://tallynine.com', // Your store URL
    'ck_bfc96e5f26d24ea8a617ce942da2790e62083e48', // Your consumer key
    'cs_a3f52cce66b70ef398e84bfb799d587da873ddd3', // Your consumer secret
    [
        'wp_api' => true, // Enable the WP REST API integration
        'version' => 'wc/v2' // WooCommerce WP REST API version
    ]
);

try {
    $results = $woocommerce->get('orders');
    $products = $woocommerce->get('products');
    $customers = $woocommerce->get('customers');
    $result = count($results);
    $customer = count($customers);
    $product = count($products);
    $query = ['date_min' => '2017-10-01', 'date_max' => '2017-10-30'];
    $sales = $woocommerce->get('reports/sales', $query);
    $sale = $sales[0]["total_sales"];

    $lastRequest = $woocommerce->http->getRequest();
    $lastResponse = $woocommerce->http->getResponse();
} catch (HttpClientException $e) {
    $e->getMessage(); // Error message.
}

if (isset($_POST['btn-update'])) {
    $status = $_POST['bookId'];
    $st = $_POST['ostatus'];

    $woocommerce->put('orders/' . $status, ['status' => $st]);
    header('Location: https://shahroznawaz.com/woo');
}

if (isset($_POST['btn-delete'])) {
    $oid = $_POST['cId'];

    $woocommerce->delete('orders/' . $oid, ['force' => true]);
    header('Location: https://shahroznawaz.com/woo');
}

// Additional widgets data
$topProducts = $woocommerce->get('reports/products/top_sellers', ['period' => 'month']);
$topProduct = $topProducts[0]['name'];
$topProductSales = $topProducts[0]['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <link rel="stylesheet" href="custom-style.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        .placeholder {
            text-align: center;
            margin-bottom: 20px;
        }

        #large {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-1">
            <h1 class="page-header">Dashboard</h1>

            <div class="row placeholders">
                <div class="col-xs-6 col-sm-3 placeholder">
                    <p id="large"><?php echo $result; ?></p>
                    <hr>
                    <span class="text-muted">New Orders</span>
                </div>
                <div class="col-xs-6 col-sm-3 placeholder">
                    <p id="large"><?php echo $customer; ?></p>
                    <hr>
                    <span class="text-muted">Customers</span>
                </div>
                <div class="col-xs-6 col-sm-3 placeholder">
                    <p id="large"><?php echo $product; ?></p>
                    <hr>
                    <span class="text-muted">All Products</span>
                </div>
                <div class="col-xs-6 col-sm-3 placeholder">
                    <p id="large"><?php echo $sale; ?></p>
                    <hr>
                    <span class="text-muted">Total Sales</span>
                </div>
                <div class="col-xs-6 col-sm-3 placeholder">
                    <p id="large"><?php echo $topProduct; ?></p>
                    <hr>
                    <span class="text-muted">Top Product</span>
                    <p id="large"><?php echo $topProductSales; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <h2 class="sub-header">Orders List</h2>
        <div class='table-responsive'>
            <table id='ordersTable' class='table table-striped table-bordered'>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $details) : ?>
                        <tr>
                            <td><?php echo $details["id"]; ?></td>
                            <td><?php echo $details["billing"]["first_name"] . ' ' . $details["billing"]["last_name"]; ?></td>
                            <td><?php echo $details["shipping"]["address_1"]; ?></td>
                            <td><?php echo $details["billing"]["phone"]; ?></td>
                            <td><?php echo $details["date_created"]; ?></td>
                            <td><?php echo $details["status"]; ?></td>
                            <td>
                                <a class='open-AddBookDialog btn btn-primary' data-target='#myModal' data-id="<?php echo $details['id']; ?>" data-toggle='modal'>Update</a>
                                <a class='open-deleteDialog btn btn-danger' data-target='#myModal1' data-id="<?php echo $details['id']; ?>" data-toggle='modal'>Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="container">
        <h2 class="sub-header">Customers List</h2>
        <div class='table-responsive'>
            <table id='customersTable' class='table table-striped table-bordered'>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Billing Address</th>
                        <th>Total Orders</th>
                        <th>Total spent</th>
                        <th>Avatar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer) : ?>
                        <tr>
                            <td><?php echo $customer["email"]; ?></td>
                            <td><?php echo $customer["first_name"] . ' ' . $customer["last_name"]; ?></td>
                            <td><?php echo $customer["billing"]["address_1"]; ?></td>
                            <td><?php echo $customer["orders_count"]; ?></td>
                            <td><?php echo $customer["total_spent"]; ?></td>
                            <td><img height='50px' width='50px' src='<?php echo $customer["avatar_url"]; ?>'></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="container">
        <h2 class="sub-header">Products List</h2>
        <div class='table-responsive'>
            <table id='productsTable' class='table table-striped table-bordered'>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Total Sales</th>
                        <th>Picture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product) : ?>
                        <tr>
                            <td><?php echo $product["sku"]; ?></td>
                            <td><?php echo $product["name"]; ?></td>
                            <td><?php echo $product["status"]; ?></td>
                            <td><?php echo $product["price"]; ?></td>
                            <td><?php echo $product["total_sales"]; ?></td>
                            <td><img height='50px' width='50px' src='<?php echo $product["images"][0]["src"]; ?>'></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update Order Status</h4>
                </div>
                <div class="modal-body">
                    <p>Order ID:</p>
                    <form action="" method="post">
                        <div class="form-group">
                            <input type="text" class="form-control" name="bookId" id="bookId" value="">

                            <p for="sel1">Select list (select one):</p>
                            <select class="form-control" id="status" name="ostatus">
                                <option>Pending Payment</option>
                                <option>processing</option>
                                <option>On Hold</option>
                                <option>completed</option>
                                <option>Cancelled</option>
                                <option>Refunded</option>
                                <option>Failed</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-block" name="btn-update">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="myModal1" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Confirm Order Deletion</h4>
                </div>
                <div class="modal-body">
                    <p>Really you want to delete order?</p>
                    <form action="" method="post">
                        <div class="form-group">
                            <input type="text" class="form-control" name="cId" id="cId" value="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" name="btn-delete">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#ordersTable, #customersTable, #productsTable').DataTable();
        });

        $(document).on("click", ".open-AddBookDialog", function() {
            var myBookId = $(this).data('id');
            $(".modal-body #bookId").val(myBookId);
        });

        $(document).on("click", ".open-deleteDialog", function() {
            var myBook = $(this).data('id');
            $(".modal-body #cId").val(myBook);
        });
    </script>
</body>

</html>
