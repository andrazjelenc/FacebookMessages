<?php require_once('static.php') ?>

<html>
	<head>
		<meta charset="utf-8"/>
		<title>Stat. obd. DB: daily_send</title>
		<link rel="stylesheet" href="css/iThing.css" type="text/css" />
		<link rel="stylesheet" type="text/css" href="css/design.css"/>
		<style type="text/css">${demo.css}</style>
		<script src="src/jquery-1.10.2.min.js"></script>
		<script src="src/jquery-ui.min.js"></script>
		<script src="src/jQDateRangeSlider-min.js"></script>
		<script src="src/list.js"></script>
		
		<script>	//people manegment
			//on checkbox checked change
			function checkboxChange(ch)
			{
				$.post("pManegment.php",
				{
					action: 'chChange',
					id: ch.value,	//person id
					status: ch.checked	//new value true/false
				},
				function(data, status) {
					if(status == "success")
					{
						sendRequest();	//recreate graph
					}
					else
					{
						console.log("AJAX Failed!");
					}
					
				});		
			}
			//on selectAll button clicked
			function selectAllCheckboxs()
			{
				$.post("pManegment.php",
				{
					action: 'selectAll'	//set all people on active=1
				},
				function(data, status){
					if(status == "success")
					{
						userList.search();
						document.getElementById("searchField").value = "";
						$("#users").find('input[type=checkbox]').each(function () {
							this.checked = true;	//set cheched=true
						});
						sendRequest();	//recreate graph
					}
					else
					{
						console.log("AJAX Failed!");
					}
				});			
			}
			
			//on deselectAll button clicked
			function selectNoneCheckboxs()
			{
				$.post("pManegment.php",
				{
					action: 'deselectAll'	//set all people on active=0
				},
				function(data, status){
					if(status == "success")
					{
						userList.search();
						document.getElementById("searchField").value = "";
						$("#users").find('input[type=checkbox]').each(function () {
							this.checked = false;	//set cheched=false
						});
						sendRequest();	//recreate graph
					}
					else
					{
						console.log("AJAX Failed!");
					}
				});
			}
		</script>
		<script>
			//auto draw graph after load
			window.addEventListener("load", function () {
				sendRequest();
			}, false);

			//send request for new graph
			function sendRequest() 
			{
				var data = $("#slider").dateRangeSlider("values");
				console.log("Min: " + data.min.getDate()+ " " + (1+data.min.getMonth())+ " " + data.min.getFullYear() );
				console.log("Max: " + data.max.getDate()+ " " + (1+data.max.getMonth())+ " " + data.max.getFullYear() );

				$.post("request.php",
				{
					minDay: data.min.getDate(),
					minMonth: (1+data.min.getMonth()),
					minYear: data.min.getFullYear(),
					maxDay: data.max.getDate(),
					maxMonth: (1+data.max.getMonth()),
					maxYear: data.max.getFullYear(),
					limit: document.getElementById( "selectMany" ).selectedOptions[0].value,
					stStolpcev: document.getElementById( "stStolpcev" ).selectedOptions[0].value,
					nacin: document.getElementById("nacin").selectedOptions[0].value
				},
				function(data, status){
					if(status == "success")
					{
						if(document.getElementById("nacin").selectedOptions[0].value == 2)
						{
							createHeatmap(data);
						}
						else
						{
							createGraph(data);
						}	
					}
					else
					{
						console.log("AJAX failed");
					}
				});
			}

			function createHeatmap(csv)
			{
				(function (H) 
				{
					var Series = H.Series,
						each = H.each;

					/*
					 * Create a hidden canvas to draw the graph on. The contents is later copied over
					 * to an SVG image element.
					 */
					Series.prototype.getContext = function () {
						if (!this.canvas) {
							this.canvas = document.createElement('canvas');
							this.canvas.setAttribute('width', this.chart.chartWidth);
							this.canvas.setAttribute('height', this.chart.chartHeight);
							this.image = this.chart.renderer.image('', 0, 0, this.chart.chartWidth, this.chart.chartHeight).add(this.group);
							this.ctx = this.canvas.getContext('2d');
						}
						return this.ctx;
					};

					/*
					 * Draw the canvas image inside an SVG image
					 */
					Series.prototype.canvasToSVG = function () {
						this.image.attr({ href: this.canvas.toDataURL('image/png') });
					};

					/*
					 * Wrap the drawPoints method to draw the points in canvas instead of the slower SVG,
					 * that requires one shape each point.
					 */
					H.wrap(H.seriesTypes.heatmap.prototype, 'drawPoints', function () {

						var ctx = this.getContext();

						if (ctx) {

							// draw the columns
							each(this.points, function (point) {
								var plotY = point.plotY,
									shapeArgs,
									pointAttr;

								if (plotY !== undefined && !isNaN(plotY) && point.y !== null) {
									shapeArgs = point.shapeArgs;

									pointAttr = (point.pointAttr && point.pointAttr['']) || point.series.pointAttribs(point);

									ctx.fillStyle = pointAttr.fill;
									ctx.fillRect(shapeArgs.x, shapeArgs.y, shapeArgs.width, shapeArgs.height);
								}
							});

							this.canvasToSVG();

						} else {
							this.chart.showLoading('Your browser doesn\'t support HTML5 canvas, <br>please use a modern browser');

							// Uncomment this to provide low-level (slow) support in oldIE. It will cause script errors on
							// charts with more than a few thousand points.
							// arguments[0].call(this);
						}
					});
					H.seriesTypes.heatmap.prototype.directTouch = false; // Use k-d-tree
				}(Highcharts));
				
				
				var decoded = JSON.parse(csv);
				
				
				var maxValue = decoded['max'];
				var dataSet = decoded['data'];
				
				
				var chart = Highcharts.chart('container', {

					series: [{
					  data: dataSet,
					  tooltip: {
						headerFormat: '#send messages<br/>',
						pointFormat: '{point.x} {point.y} <b>Sent: {point.value}</b>'
						}
					}],

					chart: {
						type: 'heatmap',
						margin: [60, 10, 80, 50]
					},


					title: {
						text: 'Heatmap frequency of sending messages',
						align: 'center',
						x: 20
					},

					xAxis: {
					  categories: [
							'00:00','00:05','00:10','00:15','00:20','00:25','00:30','00:35','00:40','00:45',
							'00:50','00:55','01:00','01:05','01:10','01:15','01:20','01:25','01:30','01:35',
							'01:40','01:45','01:50','01:55','02:00','02:05','02:10','02:15','02:20','02:25',
							'02:30','02:35','02:40','02:45','02:50','02:55','03:00','03:05','03:10','03:15',
							'03:20','03:25','03:30','03:35','03:40','03:45','03:50','03:55','04:00','04:05',
							'04:10','04:15','04:20','04:25','04:30','04:35','04:40','04:45','04:50','04:55',
							'05:00','05:05','05:10','05:15','05:20','05:25','05:30','05:35','05:40','05:45',
							'05:50','05:55','06:00','06:05','06:10','06:15','06:20','06:25','06:30','06:35',
							'06:40','06:45','06:50','06:55','07:00','07:05','07:10','07:15','07:20','07:25',
							'07:30','07:35','07:40','07:45','07:50','07:55','08:00','08:05','08:10','08:15',
							'08:20','08:25','08:30','08:35','08:40','08:45','08:50','08:55','09:00','09:05',
							'09:10','09:15','09:20','09:25','09:30','09:35','09:40','09:45','09:50','09:55',
							'10:00','10:05','10:10','10:15','10:20','10:25','10:30','10:35','10:40','10:45',
							'10:50','10:55','11:00','11:05','11:10','11:15','11:20','11:25','11:30','11:35',
							'11:40','11:45','11:50','11:55','12:00','12:05','12:10','12:15','12:20','12:25',
							'12:30','12:35','12:40','12:45','12:50','12:55','13:00','13:05','13:10','13:15',
							'13:20','13:25','13:30','13:35','13:40','13:45','13:50','13:55','14:00','14:05',
							'14:10','14:15','14:20','14:25','14:30','14:35','14:40','14:45','14:50','14:55',
							'15:00','15:05','15:10','15:15','15:20','15:25','15:30','15:35','15:40','15:45',
							'15:50','15:55','16:00','16:05','16:10','16:15','16:20','16:25','16:30','16:35',
							'16:40','16:45','16:50','16:55','17:00','17:05','17:10','17:15','17:20','17:25',
							'17:30','17:35','17:40','17:45','17:50','17:55','18:00','18:05','18:10','18:15',
							'18:20','18:25','18:30','18:35','18:40','18:45','18:50','18:55','19:00','19:05',
							'19:10','19:15','19:20','19:25','19:30','19:35','19:40','19:45','19:50','19:55',
							'20:00','20:05','20:10','20:15','20:20','20:25','20:30','20:35','20:40','20:45',
							'20:50','20:55','21:00','21:05','21:10','21:15','21:20','21:25','21:30','21:35',
							'21:40','21:45','21:50','21:55','22:00','22:05','22:10','22:15','22:20','22:25',
							'22:30','22:35','22:40','22:45','22:50','22:55','23:00','23:05','23:10','23:15',
							'23:20','23:25','23:30','23:35','23:40','23:45','23:50','23:55'],
						  //min: 0,
						  //max: 287,
						  tickInterval:12,
						  labels: { 
							step: 1,
							style: {
								fontSize:'8px'
							}
						  },
						  gridLineWidth:0,
						  lineWidth:0.5,
						  lineColor: 'rgba(0,0,0,0.75)',
						  tickWidth:0.5,
						  tickLength:3,
						  tickColor: 'rgba(0,0,0,0.75)',
					},

					yAxis: {
						categories: ['Mon', 'Tue', 'Wen', 'Thu', 'Fri','Sat','Sun'],
						min: 0,
						max: 6
					},

					colorAxis: {
					  stops: [
						[0, '#20255A'],
						[0.5, '#4B8EE2'],
						[0.9, '#AAEBFF']  
					  ],
					  min: 0,
					  max: maxValue
					}
				});
			}

			function createGraph(csv)
			{
				Highcharts.chart('container', {
					data: {
						csv: csv
					},

					title: {
						text: 'Number of send messages in time'
					},
					xAxis: {
						tickInterval: 7 * 24 * 3600 * 1000, // one week
						tickWidth: 0,
						gridLineWidth: 1,
						labels: {
							align: 'left',
							x: 3,
							y: -3
						}
					},

					yAxis: [{ // left y axis
						title: {
							text: null
						},
						labels: {
							align: 'left',
							x: 3,
							y: 16,
							format: '{value:.,0f}'
						},
						showFirstLabel: false
					}, { // right y axis

						linkedTo: 0,
						gridLineWidth: 0,
						opposite: true,
						title: {
							text: null
						},
						labels: {
							align: 'right',
							x: -3,
							y: 16,
							format: '{value:.,0f}'
						},
						showFirstLabel: false
					}],

					legend: {
						align: 'left',
						verticalAlign: 'top',
						y: 20,
						floating: true,
						borderWidth: 0
					},

					tooltip: {
						shared: true,
						crosshairs: true
					},

					plotOptions: {
						series: {
							cursor: 'pointer',
							point: {
								events: {
									click: function (e) {
										hs.htmlExpand(null, {
											pageOrigin: {
												x: e.pageX || e.clientX,
												y: e.pageY || e.clientY
											},
											headingText: this.series.name,
											maincontentText: Highcharts.dateFormat('%A, %b %e, %Y', this.x) + ':<br/> ' +
												this.y + ' visits',
											width: 200
										});
									}
								}
							},
							marker: {
								lineWidth: 1
							}
						}
					}
					
				});
			}
		</script>
	</head>
	<body>
		<!-- 
			Title
		-->
		<h1>Stat. obd. DB: daily_send</h1>
		
		<!--
				Search users for analyze
				TO-DO: 	-select/deselect all
		
				(http://listjs.com/overview/download/)
		-->
		<table>
			<tr>
				<td width="20%">
					<button onclick="selectAllCheckboxs()">Select All</button>
					<button onclick="selectNoneCheckboxs()">Select None</button>
					<div id="users">
						<input class="search" placeholder="Search" id="searchField"/>
						
						<!-- Child elements of container with class="list" becomes list items -->
						<ul class="list">
							<?php echo $friends;?>
					</div>
					<script>
						var options = {
							valueNames: [ 'name', 'value' ],
						};

						var userList = new List('users', options);
					</script>
				
				</td>
				<td>
					<!--
							Slider to select from date and to date
							(http://ghusse.github.io/jQRangeSlider/index.html)
					-->
					<div id="slider"></div>
				
					<script>
						$("#slider").dateRangeSlider({
							bounds: {min: new Date(<?php echo $minDate[0].",".($minDate[1]-1) .",".$minDate[2]; ?>), max: new Date(<?php echo $maxDate[0].",".($maxDate[1]-1) .",".$maxDate[2]; ?>)},
						});
						$("#slider").bind("valuesChanged", sendRequest);
					</script>
					
					<!--
							Select number of people to show in the graph
					-->
					<br />
					
					<div>
						Number of people:
					  <select onchange="sendRequest()" id="selectMany">
						<option value="1">1</option>
						<option value="5">5</option>
						<option selected="selected" value="10">10</option>
						<option value="15">15</option>
						<option value="20">20</option>
					  </select>
					</div>
					
					<!--
							Select number of columns to show in the graph
					-->
					<div>
						Number of columns:
					  <select onchange="sendRequest()" id="stStolpcev">
						<option selected="selected" value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
					  </select>
					</div>
					
					<!--
							Select type of graph
					-->
					<div>
						Graph type:
						<select onchange="sendRequest()" id="nacin">
							<option value="0">Summed</option>
							<option selected="selected" value="1">Part by part</option>
							<option value="2">HeatMap</option>
						</select>
					</div>
										
					<!--
							Graph
					-->
					<script src="src/highcharts.js"></script>
					<script src="src/data.js"></script>
					<script src="src/exporting.js"></script>
					<script src="src/heatmap.js"></script>
					<script src="src/highslide-full.min.js"></script>
					<script src="src/highslide.config.js" charset="utf-8"></script>
					<link rel="stylesheet" type="text/css" href="css/highslide.css" />
					
					<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
				</td>
			</tr>
		</table>
	</body>
</html>
