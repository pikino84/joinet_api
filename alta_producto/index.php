<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de productos</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="_assets/css/style.css" rel="stylesheet" id="bootstrap-css">
</head>
<body>
    <div class="wrapper-charger" >
        <img src="_assets/img/loading.gif" >
    </div>
    <div class="container register-form">
        <div class="form">
            <div class="note">
                <p>Alta y actualización de producto</p>
            </div>
            <form class="frm">
                <div  class="form-content">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="SKU *" value="" name="sku" required/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <button  type="submit" class="btnSubmit">Submit</button>
                        </div>
                    </div>
                    <p class="error"></p>
                    <p class="message"></p>
                    <p class="wrapper_link"><a class="link" target="_blank" href=""></a></p>
                </div>
            </form>
        </div>
    </div>   
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="_assets/js/update_product.js"></script>
</body>
</html>




