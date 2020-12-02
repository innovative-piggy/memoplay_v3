<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION["admin"])) {
    header('location: login.php');
}

$connect = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DBNAME);
$query = "SELECT * FROM zip order by id ASC";
$result = mysqli_query($connect, $query);
?>  

<!DOCTYPE html>  
<html>
	<head>  
		<title>Upload </title>  

		<link rel="stylesheet" href="./assets/bootstrap.css" />  
		<link rel="stylesheet" href="./assets/dataTables.bootstrap4.min.css" />  

		<script src="./assets/jquery-3.5.1.js"></script>  
		<script src="./assets/jquery.dataTables.min.js"></script>
		<script src="./assets/dataTables.bootstrap4.min.js"></script>  

		<link rel="stylesheet" href="./assets/toastr.min.css" />  
		<script src="./assets/toastr.min.js"></script>
		<script src="./assets/jquery.progress.js"></script>
		<link rel="stylesheet" href="./assets/jquery-confirm.min.css">
		<script src="./assets/jquery-confirm.min.js"></script>

		<style>
			#info{ display: none }
		</style>
	</head>  

	<body>  
		<div class="container"> 
			<nav class="navbar navbar-expand-lg navbar-light bg-light">
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto">
					<li class="nav-item ">
						<a class="nav-link" href="index.php">List orders</a>
					</li>
					<li class="nav-item active">
						<a class="nav-link" href="#" style="font-weight: 600">Bulk Generate Zip</a>
					</li>
					</ul>
					<form class="form-inline my-2 my-lg-0">
						<a class="btn btn-danger my-2 my-sm-0" href="logout.php">Logout</a>
					</form>
				</div>
			</nav>
			<div class="row mt-5 mb-3">
				<div class="col-md-6">
					<h4>Select START and END order </h4>  
				</div>
				<div class="col-md-6" style="text-align:end;">
					<input type="checkbox" id="chk_lowquality" <?php if ($_SESSION['lowquality']) echo 'checked'; ?>> <span style="margin-left: 5px; margin-right:30px;"> Low Quality </span>
				</div>
			</div>
			<!-- <form method="get" action="create_zip.php"> -->
			<div>
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label for="strtorder">Start order</label>
							<input class="form-control" id="startorder" type="number" min="0" name="start" placeholder="start order id" required="">
						</div>
					</div>  
					<div class="col-md-3">  
						<div class="form-group">
							<label for="endorder">End order</label>
							<input class="form-control" id="endorder"  type="number" min="0" name="end" placeholder="end order id" required="">
						</div>
					</div>  
					<div class="col-md-12">
						<svg id="progress"></svg>
					</div>
					<div class="col-md-12">  
						<button id="generatebtn"style="margin-top:10px;" class="btn btn-info">Generate</button> 
						<span id="info">Please wait while generating the zip file....</span> 
					</div> 
				</div>
				<div style="clear:both"></div>
			<!-- </form>   -->
			</div>

			<br /><br /><br />

			<div class="table-responsive" >  
				<table id="example" class="table table-striped table-bordered" style="width:100%" >  
					<thead>
						<tr>  
							<th>Date</th>
							<th>Order Start</th>
							<th>Order End</th>
							<th>ZIP</th>
							<th>Fail List</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php while ($row = mysqli_fetch_array($result)): ?>
						<tr>
							<td><?php echo date('Y-m-d (H:i:s)', $row["datep"]); ?></td>
							<td><?php echo $row["startp"]; ?></td>
							<td><?php echo $row["endp"]; ?></td>
							<td><a target="_blank" href="zip/<?php echo $row["datep"]; ?>_<?php echo $row["startp"]; ?>_<?php echo $row["endp"]; ?>.zip">Download</a></td>
							<td>
								<?php if (!is_null($row['fails']) && $row['fails'] != ""): ?>
									<a href="#" class="show_fail_list" data="<?php echo htmlspecialchars($row['fails']);?>">Download</a>
								<?php endif; ?>
							</td>
							<td><a href="deleteZip.php?id=<?php echo $row["id"]; ?>&datep=<?php echo $row["datep"]; ?>&startp=<?php echo $row["startp"]; ?>&endp=<?php echo $row["endp"]; ?>" onclick="return confirm('Are you sure, you want to delete it?')">Delete</a></td>
						</tr>
						<?php endwhile; ?> 
					</tbody> 
				</table>
			</div>
		</div>  
	</body>  
	<script>
		var progress;
		var startorder = 1, endorder = 1;
		var orderids = [], tot = 0, cnt = 0, fail_orders = [];

		$(document).ready(function() {  
			$('#example').DataTable();
			progress = $("#progress").Progress({
				percent: 0,
				width: 460,
				height: 37,
				barColor:'#46CFB0',
				fontSize: 16,
				increaseSpeed: 10
			});
		});

		$("#generatebtn").click(function() {
			$('#generatebtn').prop('disabled', true);

			startorder = $('#startorder').val();
			endorder = $('#endorder').val();
			if (startorder == '' || endorder == '') {
				toastr.error('Invalid input value!');
				return;
			}
			if (parseInt(startorder) > parseInt(endorder)) {
				$('#startorder').val(endorder);
				$('#endorder').val(startorder);
				toastr.error('Invalid input value!');
				return;
			}
			
			$.post(
				'create_zip.php', 
				{ oper: 'gettot', startorder: startorder, endorder: endorder }
			).done(function (rlt) {
				rlt = JSON.parse(rlt);
				if (rlt.status == 200) {
					var tot_exist = rlt.ids.length - rlt.noexist_ids.length;
					$.confirm({
						title: 'Confirm!',
						content: tot_exist + ' PNGs already exist of ' + rlt.ids.length + ' PNGs to be generated.',
						escapeKey: 'cancel',
						buttons: {
							skipBtn: {
								text: 'Skip?',
								btnClass: 'btn-success',
								keys: ['enter'],
								action: function() {
									cnt = 0;
									orderids = rlt.noexist_ids;
									tot = orderids.length;
									toastr.success(rlt.noexist_ids.length + ' of ' + rlt.ids.length + ' PNGs generating...');
									fail_orders = [];
									createimage();
								}
							},
							replaceBtn: {
								text: 'Replace?',
								btnClass: 'btn-danger',
								action: function() {
									cnt = 0;
									orderids = rlt.ids;
									tot = orderids.length;
									toastr.success(rlt.ids.length + ' PNGs regenerating entirely...');
									fail_orders = [];
									createimage();
								}
							},
							cancel: function() {
								$('#generatebtn').prop('disabled', false);
							}
						}
					});
				} else {
					toastr.error(rlt.msg);
				}
			}).catch(function (err) {
				toastr.error('Network or Server Error!');
				$('#generatebtn').prop('disabled', false);
			});
		});

		// Send request to create the image RECURSIVELY
		function createimage() {
			if (cnt >= tot) {
				toastr.success('Creating Images Over!');
				console.log(fail_orders);
				$.confirm({
					title: 'Continue?',
					content: 'PNG-Generation Over! You will be automatically create ZIP in 10 seconds.',
					autoClose: 'zipBtn|10000',
					escapeKey: 'cancel',
					buttons: {
						zipBtn: {
							text: 'Create ZIP',
							action: function () {
								createzip();
							}
						},
						cancel: function () {
							$('#generatebtn').prop('disabled', false);
						}
					}
				});
				return;
			}
			var orderid = orderids[cnt][0];
			$.post(
				'create_zip.php',
				{ oper: 'createimage', orderid: orderid }
			).done(function (rlt) {
				rlt = JSON.parse(rlt);
				if (rlt.status == 200 || rlt.status == 201) {
					cnt++;
					progress.percent(cnt * 100 / tot);
					if (rlt.status == 200) toastr.success(rlt.orderid + ' Created!');
					else toastr.error(rlt.orderid + 'can not create! ' + rlt.msg);
					createimage();
				} else {
					toastr.error(rlt.msg);
				}
			}).catch(function (err) {
				toastr.error(orderid + ' Generation Failure cause of ' + err.statusText + '!');
				fail_orders.push({ order_id_n: orderid, reason: err.statusText });
				// even if error occurs, continue generation
				cnt++;
				progress.percent(cnt * 100 / tot);
				createimage();
			});
		}

		function createzip() {
			toastr.warning('Generating ZIP file...');
			$.post(
				'create_zip.php',
				{ oper: 'createzip', startorder: startorder, endorder: endorder, fail_orders: fail_orders }
			).done(function (rlt) {
				rlt = JSON.parse(rlt);
				if (rlt.status == 200) {
					toastr.success(rlt.msg);
					setTimeout(function() {
						$('#generatebtn').prop('disabled', false);
						location.reload();
					}, 1000 * 2);
				} else {
					toastr.error(rlt.msg);
				}
			}).catch(function (err) {
				toastr.error(err.statusText);
				$('#generatebtn').prop('disabled', false);
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

		$('.show_fail_list').click(function(obj) {
			var json = JSON.parse(obj.target.getAttribute('data'));
			var text = "";
			for (var i = 0; i < json.length; i++) {
				text += (i + 1) + "\t";
				text += json[i].order_id_n + "\t\t";
				text += json[i].reason + "\n";
			}
			var element = document.createElement('a');
			element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
			element.setAttribute('download', 'fail_list.txt');
			element.style.display = 'none';
			document.body.appendChild(element);
			element.click();
			document.body.removeChild(element);
		})
	</script>
</html>  
