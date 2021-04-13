<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Product Filters in Codeigniter using Ajax</title>
    
    <!-- Bootstrap Core CSS -->    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">    
    <!-- Custom CSS -->        
    <style>
        #loading {
            text-align: center;
            background: url('<?=base_url()?>asset/loader.gif') no-repeat center;
            height: 150px;
        }
    </style>
</head>
<body>
    <!-- Page Content -->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 align="center">Product Filters in Codeigniter using Ajax</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">      
                <div class="list-group">
                    <h3>Search</h3>
                    <input type="text" id="search" class="form-control" placeholder="search...">
                </div>          
                <div class="list-group">
                    <h3>Price</h3>
                    <input type="hidden" id="hidden_minimum_price" value="0" />
                    <input type="hidden" id="hidden_maximum_price" value="65000" />
                    <p id="price_show">1000 - 65000</p>
                    <div id="price_range"></div>
                </div>
                <div class="list-group">
                    <h3>Brand</h3>
                    <?php 
                    foreach($brand_data->result_array() as $row)
                    {
                    ?>
                    <div class="list-group-item checkbox">
                        <label>
                            <input type="checkbox" class="common_selector brand" value="<?=$row['product_brand'];?>"> <?=$row['product_brand'];?>
                        </label>
                    </div>
                    <?php
                    }
                    ?>
                </div>
                <div class="list-group">
                    <h3>RAM</h3>
                    <?php 
                    foreach($ram_data->result_array() as $row)
                    {
                    ?>
                    <div class="list-group-item checkbox">
                        <label>
                            <input type="checkbox" class="common_selector ram" value="<?=$row['product_ram'];?>"> <?=$row['product_ram'];?> GB
                        </label>
                    </div>
                    <?php
                    }
                    ?>
                </div>
                <div class="list-group">
                    <h3>Internal Storage</h3>
                    <?php 
                    foreach($product_storage->result_array() as $row)
                    {
                    ?>
                    <div class="list-group-item checkbox">
                        <label>
                            <input type="checkbox" class="common_selector storage" value="<?=$row['product_storage'];?>"> <?=$row['product_storage'];?> GB
                        </label>
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </div>

            <div class="col-md-9">
                <div align="center" id="pagination_link"></div>
                <br />
                <br />
                <div class="row filter_data"></div>
                <br />
                <br />                
            </div>
        </div>
    </div>
    <!-- javascript cdn -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>      
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {            
            var timer
            var delay = 600

            filter_data(1) // get all data and display page 1

            function filter_data(page) {                
                $('.filter_data').html("<div id='loading'></div>")
                var action = 'fetch_data'                
                var keyword = $('#search').val()
                var minimum_price = $('#hidden_minimum_price').val()
                var maximum_price = $('#hidden_maximum_price').val()
                var brand = get_filter('brand')
                var ram = get_filter('ram')
                var storage = get_filter('storage')                                
                $.ajax({                               
                    url: "<?=base_url()?>products/fetch_data/"+page,
                    method: "POST",
                    dataType: "JSON",
                    data: {
                        action: action,                    
                        keyword: keyword,
                        minimum_price: minimum_price,
                        maximum_price: maximum_price,
                        brand: brand,
                        ram: ram,
                        storage: storage
                    },
                    success: function(data) {                        
                        $('.filter_data').html(data.product_list)
                        $('#pagination_link').html(data.pagination_link)
                    },
                    error: function (jqXHR, exception) {
                        var msg = '';
                        if (jqXHR.status === 0) {
                            msg = 'Not connect.\n Verify Network.';
                        } else if (jqXHR.status == 404) {
                            msg = 'Requested page not found. [404]';
                        } else if (jqXHR.status == 500) {
                            msg = 'Internal Server Error [500].';
                        } else if (exception === 'parsererror') {
                            msg = 'Requested JSON parse failed.';
                        } else if (exception === 'timeout') {
                            msg = 'Time out error.';
                        } else if (exception === 'abort') {
                            msg = 'Ajax request aborted.';
                        } else {
                            msg = 'Uncaught Error.\n' + jqXHR.responseText;
                        }                        
                        $('.filter_data').html(msg)
                    },
                })
            }            

            function get_filter(class_name) {
                var filter = []
                $('.'+class_name+':checked').each(function(){
                    filter.push($(this).val())
                })
                return filter
            }

            // pagination link click handler
            $(document).on('click', '.pagination li a', function(event) {
                event.preventDefault()                
                if(typeof $(this).data('ci-pagination-page') !== 'undefined') {                    
                    filter_data($(this).data('ci-pagination-page'))
                }                
            })

            // checkbox click handler
            $('.common_selector').click(function() {
                filter_data(1);
            })

            // price slider
            $('#price_range').slider({
                range: true,
                min: 1000,
                max: 65000,
                values: [1000,65000],
                step: 500,
                stop: function(event, ui) {
                    $('#price_show').html(ui.values[0] + ' - ' + ui.values[1])
                    $('#hidden_minimum_price').val(ui.values[0])
                    $('#hidden_maximum_price').val(ui.values[1])
                    filter_data(1)
                }
            })

            $('#search').bind('input', function() {
                window.clearTimeout(timer)
                timer = window.setTimeout(function() {                    
                    filter_data(1)
                }, delay)
            })
        })        
    </script>
</body>
</html>