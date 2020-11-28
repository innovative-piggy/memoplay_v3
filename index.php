<?php
require_once 'config.php';

session_start();
if (!isset($_SESSION["admin"])) header('location: login.php');
?>

<!DOCTYPE html>  
<html lang="fr-FR">
	<head>  
		<title>Upload </title>  
		<meta charset="UTF-8">

		<link rel="stylesheet" href="./assets/bootstrap.css" />  
		<link rel="stylesheet" href="./assets/dataTables.bootstrap4.min.css" />  

		<script src="./assets/jquery-3.5.1.js"></script>  
		<script src="./assets/jquery.dataTables.min.js"></script>  
		<script src="./assets/dataTables.bootstrap4.min.js"></script>

		<link rel="stylesheet" href="./assets/jquery.modal.css">
		<script src="./assets/jquery.modal.js"></script>
		<link rel="stylesheet" href="./assets/toastr.min.css" />
		<script src="./assets/toastr.min.js"></script>
		<script src="./assets/jquery.blockui.js"></script>
		<style>
			.modaler {
				display: flex;
				justify-content: center;
				align-items: center;
				top: -5%;
			}
			.modal {
				height: auto;
			}
			.modal .footer {
				text-align: end;
			}
			#preview_modal .form-body img {
				border: solid 1px slateblue;
			}
			hr {
				margin-top: 0.5rem !important;
				margin-bottom: 0.5rem !important;
			}
		</style>
	</head>  

	<body>
		<div class="container">
			<nav class="navbar navbar-expand-lg navbar-light bg-light">
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item ">
							<a class="nav-link" href="index.php" style="font-weight: 600">List orders</a>
						</li>
						<li class="nav-item active">
							<a class="nav-link" href="download_bulk.php" >Bulk Generate Zip</a>
						</li>
					</ul>
					<form class="form-inline my-2 my-lg-0">
						<a class="btn btn-danger my-2 my-sm-0" href="logout.php">Logout</a>
					</form>
				</div>
			</nav>
			<br />
			<div class="row">
				<div class="col-md-6">
					<h4 class="text-center">Load Data from Shopify directly</h4>
					<div class="text-center" style="border: solid 1px;padding: 10px 10px 10px 10px;margin-bottom: 10px;">
						<button id="btn_direct_load" style="margin-top:10px;" class="btn btn-success"> Load & Update DB </button>
					</div>
				</div>
				<div class="col-md-6" style="display:none;">
					<h4 class="text-center">Import CSV File generated with Xporter</h4>  
					<form id="upload_csv" method="post" enctype="multipart/form-data" style="border: solid 1px;padding: 10px 10px 10px 10px;margin-bottom: 10px;">
						<div class="row">
							<div class="col-md-6">  
								<input type="file" name="orders_file" style="margin-top:15px;" />  
							</div>  
							<div class="col-md-6 text-right">  
								<input disabled class="btn btn-info" type="submit" name="upload" id="upload" value="Upload" style="margin-top:10px;" />  
							</div> 
						</div> 
						<div style="clear:both"></div>  
					</form>  
				</div>
			</div>
			<hr/>
			<div style="float:right;">
				<input type="checkbox" id="chk_lowquality" <?php if ($_SESSION['lowquality']) echo 'checked'; ?>> <span style="margin-left: 5px; margin-right:30px;"> Low Quality </span>
				<a class="btn btn-danger my-2 my-sm-0" href="deleteAll.php?all=1" onclick="return confirm('Are you sure, you want to delete all?')">Delete All</a>
			</div>
			<br/><br/>
			<div class="table-responsive" id="employee_table">  
				<table id="example" class="table table-bordered table-hover" style="width:100%">  
					<thead>
						<tr>  
							<th>OrderID</th>
							<th>Date</th>
							<th>Name - Email - Shipping</th>
							<th>Musique</th>
							<th>Personnalisee</th>
							<th>Image</th>
							<th>Action</th>
						</tr>  
					</thead>
					<tbody>
					</tbody>
				</table>  
			</div>
		</div>

		<div id="update_modal" class="modal fade">
			<div class="header">
				<h2>Edit</h2>
			</div>
			<div class="content">
				<div class="form-body">
					<input id="order_id_n" type="hidden">
					<div class="form-group" style="display: flex;">
						<label class="col-md-4 control-label" style="text-align: end; margin-top: auto;">
							text musique
						</label>
						<div class="col-md-8">
							<input id="text_music" type="text" class="form-control" style="margin-top: 1rem;">
						</div>
					</div>
					<div class="form-group" style="display: flex;">
						<label class="col-md-4 control-label" style="text-align: end; margin-top: auto;">
							text personnalisee
						</label>
						<div class="col-md-8">
							<input id="text_personal" type="text" class="form-control" style="margin-top: 1rem;">
						</div>
					</div>
				</div>
			</div>
			<div class="footer">
				<button id="btn_save" class="btn btn-success">Save</button>
				<button class="btn btn-primary" data-dismiss="modal">Cancel</button>
			</div>
		</div>

		<div id="preview_modal" class="modal fade">
			<div class="header">
				<h2>Preview</h2>
			</div>
			<div class="content">
				<div class="form-body" style="display: flex;justify-content: center;align-items: center;background-image: linear-gradient(140deg, #EADEDB 0%, #BC70A4 100%);">
				</div>
			</div>
			<div class="footer">
				<button class="btn btn-primary" data-dismiss="modal">Close</button>
			</div>
		</div>

		<div id="upsell_modal" class="modal fade">
			<div class="header">
				<h2>Edit UPSELL Order</h2>
			</div>
			<div class="content">
				<div class="form-body">
					<div class="form-group" style="display: flex;">
						<label class="col-md-4 control-label" style="text-align: end; margin-top: auto;">
							OrderID (order_id_n)
						</label>
						<div class="col-md-8">
							<input id="upsell_order_id_n" type="text" class="form-control" style="margin-top: 1rem;" readonly>
						</div>
					</div>
					<div class="form-group" style="display: flex;">
						<label class="col-md-4 control-label" style="text-align: end; margin-top: auto;">
							Size
						</label>
						<div class="col-md-8">
							<input id="size" type="text" class="form-control" style="margin-top: 1rem;" readonly>
						</div>
					</div>
					<div class="form-group" style="display: flex;">
						<label class="col-md-4 control-label" style="text-align: end; margin-top: auto;">
							Customer Name
						</label>
						<div class="col-md-8">
							<input id="customer_name" type="text" class="form-control" style="margin-top: 1rem;" readonly>
						</div>
					</div>
					<div class="form-group" style="display: flex;">
						<label class="col-md-4 control-label" style="text-align: end; margin-top: auto;">
							Original OrderID (order_id_n)
						</label>
						<div class="col-md-8">
							<!-- <input id="original_order_id_n" type="text" class="form-control" style="margin-top: 1rem;"> -->
							<select id="original_order_id_n" class="form-control"></select>
						</div>
					</div>
				</div>
			</div>
			<div class="footer">
				<button id="btn_upsellsave" class="btn btn-success">Save</button>
				<button class="btn btn-primary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</body>

	<script>
		var DELAY = 3;
		var my_table;
		$(document).ready(function() {
			my_table = $('#example').DataTable({
				processing: true,
				serverSide: true,
				ajax: "orders_table.php",
				lengthMenu: [[5, 10, 20, 50, -1], [5, 10, 20, 50, "All"]],
				pageLength: 5,
				columnDefs: [{orderable: !1, targets: [5]}, {orderable: !1, targets: [6]}],
				order: [[0, "desc"]],
				"fnCreatedRow": function( nRow, aData, iDataIndex ) {
					$(nRow).attr('id', aData[7]);
				}
			});
			$('#upload_csv').on("submit", function(e) {
				e.preventDefault(); //form will not submitted  
				$.blockUI({ message: '<h5><img src="assets/busy.gif"/>&nbsp;Loading Data from CSV... </h5>' });
				$.ajax({  
					url: "import.php",  
					method: "POST",  
					data: new FormData(this),  
					contentType: false,          // The content type used when sending data to the server.  
					cache: false,                // To unable request pages to be cached  
					processData: false,          // To send DOMDocument or non processed data file it is set to false  
					success: function(rlt) {
						rlt = JSON.parse(rlt);
						if (rlt.status == 200) {
							toastr.success("Loaded " + rlt.tot + " records successfully!<br>" + rlt.unique_upsell_cnt + " UPSELL orders processed.");
							setTimeout(function () {
								location.reload();
							}, 1000 * DELAY);
						} else {
							toastr.error(rlt.msg);
							$.unblockUI();
						}
					},
					complete: function() {
						$.unblockUI();
					}
				});
			});
		});

		function update(order_id_n) {
			var text_music = $('#tr_' + order_id_n + ' > td:nth-child(4)').text();
			var text_personal = $('#tr_' + order_id_n + ' > td:nth-child(5)').text();
			$('#order_id_n').val(order_id_n);
			$('#text_music').val(text_music);
			$('#text_personal').val(text_personal);
			$('#update_modal').modal();
		}

		function generate(orderid) {
			$.blockUI({ message: '<h5><img src="assets/busy.gif"/>&nbsp;&nbsp;&nbsp; Generating PNG file... </h5>' });
			$.post(
				'create_zip.php',
				{ oper: 'createimage', orderid: orderid }
			).done(function (rlt) {
				$.unblockUI();
				rlt = JSON.parse(rlt);
				if (rlt.status == 200) {
					toastr.success(rlt.orderid + ' Created!');
					preview(rlt.filename);
					$('#tr_' + orderid + ' > td:nth-child(6)').html("<a href=\"javascript:generate('" + orderid + "')\">Generate</a><hr><a href=\"javascript:preview('" + rlt.filename + "')\">Preview</a>");
				} else {
					toastr.error(rlt.msg);
				}
			}).catch(function (err) {
				$.unblockUI(); 
				toastr.error('Network or Server Error!');
			});
		}

		function preview(filename) {
			$('#preview_modal .form-body').html("<a target='_blank' href='<?php echo DIR; ?>" + filename + "'><img src='preview.php?fn=" + filename + "' /></a>");
			$('#preview_modal').modal();
		}

		$('#btn_save').click(function() {
			var order_id_n = $('#order_id_n').val();
			var text_music = $('#text_music').val();
			var text_personal = $('#text_personal').val();
			$.post(
				'create_zip.php',
				{
					oper: 'update_music_personal', 
					order_id_n: order_id_n,
					text_music: text_music,
					text_personal: text_personal
				}
			).done(function (rlt) {
				rlt = JSON.parse(rlt);
				if (rlt.status == 200) {
					toastr.success(rlt.msg);
					$('#tr_' + order_id_n + ' > td:nth-child(4)').text(text_music);
					$('#tr_' + order_id_n + ' > td:nth-child(5)').text(text_personal);
					$('.modal').modal('hide');
				} else {
					toastr.error(rlt.msg);
				}
			}).catch(function (err) {
				toastr.error('Network or Server Error!');
			});
		});

		function edit_upsell(upsell_order_id_n, size, customer_name) {
			$('#upsell_order_id_n').val(upsell_order_id_n);
			$('#size').val(size);
			$('#customer_name').val(customer_name);
			upsell_unique = false;
			$.post('create_zip.php', { oper: 'find_original_of_upsell', upsell_order_id_n: upsell_order_id_n })
			.done(function (rlt) {
				rlt = JSON.parse(rlt);
				$('#original_order_id_n').html('');
				if (rlt.status == 200) {
					var ids = rlt.original_order_ids;
					for (var i = 0; i < ids.length; i++) {
						$('#original_order_id_n').append("<option value='" + ids[i] + "'>" + ids[i] + "</option>");
					}
					$('#upsell_modal').modal();
				} else {
					toastr.error(rlt.msg);
				}
			}).catch(function (err) {
				toastr.error('Network or Server Error!');
			});
		}

		$('#btn_upsellsave').click(function() {
			var upsell_order_id_n = $('#upsell_order_id_n').val();
			var original_order_id_n = $('#original_order_id_n').val();
			$.post(
				'create_zip.php',
				{ oper: 'edit_upsell_order', upsell_order_id_n: upsell_order_id_n, original_order_id_n: original_order_id_n }
			).done(function (rlt) {
				rlt = JSON.parse(rlt);
				if (rlt.status == 200) {
					$('#upsell_modal').modal('hide');
					toastr.success(rlt.msg);
					$('#tr_' + upsell_order_id_n + ' > td:nth-child(4)').text(rlt.text.titre_musique);
					$('#tr_' + upsell_order_id_n + ' > td:nth-child(5)').text(rlt.text.phrase_personnalisee);
					$('#tr_' + upsell_order_id_n + ' > td:nth-child(7)').html("<a href=\"javascript:update('" + upsell_order_id_n + "')\">Edit</a><hr><a href=\"javascript:delete_record('" + upsell_order_id_n + "')\">Delete</a>");
				} else {
					toastr.error(rlt.msg);
				}
			}).catch(function (err) {
				toastr.error('Network or Server Error!');
			});
		});

		function delete_record(order_id_n) {
			$.get('deleteRecord.php?id=' + order_id_n).done(function (rlt) {
				rlt = JSON.parse(rlt);
				if (rlt.status == 200) {
					toastr.success(rlt.msg);
					my_table.row('#tr_' + order_id_n).remove().draw();
				} else {
					toastr.error(rlt.msg);
				}
			}).catch(function (err) {
				toastr.error('Network or Server Error!');
			});
		}

		$('#chk_lowquality').click(function() {
			$.post('create_zip.php', { oper: "lowquality" })
			.done(function(rlt) {
				rlt = JSON.parse(rlt);
				if (rlt.status == 200) {
					$('#chk_lowquality').prop('checked', rlt.lowquality);
				}
			})
		});

		$('#btn_direct_load').click(function() {
			$.blockUI({ message: '<h5><img src="assets/busy.gif"/>&nbsp;Loading Data from Shopify... </h5>' });

			$.post('direct_load.php', {})
			.done(function(rlt) {
				$.unblockUI();
				rlt = JSON.parse(rlt);
				if (rlt.status == 200) {
					toastr.success("Loaded " + rlt.success_cnt + " of " + rlt.tot + " records successfully!<br>" + rlt.upsell_cnt + " UPSELL orders processed.");
					setTimeout(function () {
						location.reload();
					}, 1000 * DELAY);
				} else {
					toastr.error(rlt.msg);
				}
			}).catch(function(err) {
				$.unblockUI();
				toastr.error("Network or Server Error!");
			})
		});
		
	</script>
</html>
