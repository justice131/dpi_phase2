<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>Data Insight of Macquarie Catchment</title>
        <?php include("../../common.scripts/all_import_scripts.html"); ?>
        <?php include("../../common.scripts/pc_import_scripts.html"); ?>
        <script src="../../border/MacquarieBogan_watersource_centroids.geojson"></script>
    </head>
    <body style="background-color:#F3F3F4;">
        <?php include("../../common.scripts/navigator.html"); ?>
	<div id="page-wrapper" class="gray-bg dashboard"  style="padding-bottom:20px">
		<div class="row">
			<div class="box-container" style="width:22%;" id="map_panel">
				<div class="box">
					<div class="box-title">
                                            <div id="s0_title">
                                                <span style="font-size:18px; font-weight:bold; margin-bottom: 0; height: 48px;">Water Sources of Macquarie Catchment--Risk to catchment due to drought</span>
                                            </div>
					</div>
					<div class="box-content">
						<div id="map"></div>
					</div>       
                                </div>
			</div>
			<div class="box-container" style="width:46.5%;">
                            <div class="box">
                                <div class="box-title">
                                        <h4><b>Parallel Coordinates</b></h4>                                                
                                </div>
                                <div class="box-content">
                                    <div id="parrallel_coordinate" class="parcoords"></div>
                                </div>
                            </div>
			</div>
			<div class="box-container" style="width:31.5%;">
                            <div class="box">
                                <div class="box-title">
                                        <h4><b>Water Source List</b></h4>                                               
                                </div>  
                                <div class="box-content">
                                    <div id="grid"></div>
                                </div>
                            </div>
			</div>                   
		</div>
	</div>
        <div class="se-pre-con"></div>
        
        <script type="text/javascript">
            // Show preloader
            $(window).load(function() {
            $(".se-pre-con").fadeOut("slow");;
            });
            
            // Set the page and component height
            pageHeight = window.screen.height*heightRatio*0.95;//get the page height
            window.onload=function(){
                document.getElementById("map").style.height = pageHeight + "px";//set height of map
                document.getElementById("parrallel_coordinate").style.height = pageHeight + "px";
                document.getElementById("grid").style.height = pageHeight + "px";
            }
            
            var removeLayer = function (feature) {
                for (var i = 0; i < feature.length; i++){     
                    map.removeLayer(feature[i]);
                }               
            };
            
            /*Functions section*/
            function showIt(d) {
                    return d > -1 ? 0.75 : 0;
            }

            function resetHighlight(e) {
                    geojson.resetStyle(e.target);
                    //info.update();
            }

            function zoomToFeature(e) {
                    var layer = e.target;
                    info.update(layer.feature.properties);
                    //map.fitBounds(e.target.getBounds());
            }

            function onEachFeature(feature, layer) {
                    layer.on({
                            mouseover: highlightFeature,
                            mouseout: resetHighlight,
                            click: zoomToFeature
                    });
            }

            function highlightFeature(e) {
                    var layer = e.target;
                    if (layer._layers) {
                            console.log(layer);
                            layer.eachLayer(function (myLayer) {
                                    console.log(myLayer);
                                    myLayer.setStyle({
                                            weight: myLayer.options ? (myLayer.options.opacity > 0 ? 3 : 0) : 0,
                                            color: '#666',
                                            dashArray: '',
                                    });
                            });
                    } else {
                            layer.setStyle({
                                    weight: layer.options ? (layer.options.opacity > 0 ? 3 : 0) : 0,
                                    color: '#666',
                                    dashArray: '',
                            });
                    }

                    if (!L.Browser.ie && !L.Browser.opera) {
                            layer.bringToFront();
                    }

                    //info.update(layer.feature.properties);
            }
            /*Function section*/
            
            function toThousands(num) {
                if (isNaN(num)) { 
                    throw new TypeError("num is not a number"); 
                } 
                var groups = (/([\-\+]?)(\d*)(\.\d+)?/g).exec("" + num), 
                    mask = groups[1],
                    integers = (groups[2] || "").split(""),
                    decimal = groups[3] || "",
                    remain = integers.length % 3; 
  
                var temp = integers.reduce(function(previousValue, currentValue, index) { 
                if (index + 1 === remain || (index + 1 - remain) % 3 === 0) { 
                    return previousValue + currentValue + ","; 
                } else { 
                    return previousValue + currentValue; 
                } 
                }, "").replace(/\,$/g, ""); 
                    return mask + temp + decimal; 
            }

            /*overall variables*/
            var myCols = [
                    '#ff3333',//Red
                    '#ff8533',//Orange
                    '#33ff33'//Green
            ];


            var lgas = MacquarieBogan_unregulated;
            var padding = 35;
            var layer, overlay;
            var filtered;
            var isSelected = false;
            var lga = new Array();
            /*overall variables*/
                        
            var map = L.map('map',{zoomControl: false}).setView([-29.0, 134.7], 4.4);
            L.control.zoom({
                position:'bottomleft'
            }).addTo(map);
            
            var myToken = 'pk.eyJ1IjoiZHlobCIsImEiOiJjaWtubG5uMWYwc3BmdWNqN2hlMzFsNDhvIn0.027tda69GVKbxiPnkEBDAw';
            L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=' + myToken, {
                    maxZoom: 18,
                    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
                            '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
                            'Imagery © <a href="http://mapbox.com">Mapbox</a>',
                    id: 'mapbox.outdoors'
            }).addTo(map);
            
            var Mac_unregulated = L.geoJSON(lgas, {
                onEachFeature: function onEach(feature, layer){
                    layer.setStyle({color: 'grey', weight: 1.2, fillOpacity: 0.1});
                }
            }).addTo(map);
            map.setView([-31.8, 148.5], 8);       
            
//            function getColorScalar(d) {
//                if(d<=Math.floor(max_row/3)){
//                return myCols[2];
//                }else if(d<=Math.ceil(2*max_row/3)){
//                return myCols[1];
//                }else{
//                return myCols[0];
//                }
//            }
            
            function getColorScalar(d) {
                if(d >= 0 && d <= 0.3){
                return myCols[0];
                }else if(d > 0.3 && d <= 0.55){
                return myCols[1];
                }else{
                return myCols[2];
                }
            }
            
            function style(feature) {
                    return {
                            weight: 1,
                            opacity: showIt(1),
                            color: 'white',
                            dashArray: '3',
                            fillOpacity: 0.8 * showIt(feature.properties.DSI),
                            fillColor: getColorScalar(feature.properties.DSI)
                    };
            }
            var max_row=0;//Get the row number of ranking file
            d3.csv("../../pc.csv/dsi_macquaire.csv", function (data) {
                _.each(data, function (d, i) {
                max_row++;
                });
            });

            parrallel_coordinate.style.display = 'block';
            grid.style.display = 'block';
            // control that shows state info on hover
            info = L.control({position: 'topright'});
            info.onAdd = function (map) {
                    this._div = L.DomUtil.create('div', 'info');
                    this.update();
                    return this._div;
            };
            info.update = function (props) {
                    this._div.innerHTML = (props?
                            '<h4>' + props.WATER_SOUR + '</h4>'+
                                    'Irrigated Area: '+ '<b>' + toThousands(Math.round(props.irrigated_area*10)/10) + ' Ha' + '</b>' + '<br />'+
                                    'Irrigation Value: '+ '<b>$'+ toThousands(Math.round(props.irrigation_value*100)/100)+'M' + '</b>'+'<br />'+
                                    'Employment (Irrigation): '+ '<b>'+toThousands(props.employment_irrigation) +'</b>'+'<br />'+
                                    'Total Entitlement: '+ '<b>'+ toThousands(props.total_entitlement) +' ML/year'+ '</b>' +'<br />'+
                                    'Mean Flow: '+ '<b>'+ toThousands(Math.round(props.mean_flow*10*365)/10) + ' ML/year'+'</b>' +'<br />'+
                                    'DSI: '+ '<b>'+ Math.round(props.DSI*100)/100 + '</b>'+'<br />'+
                                    'FUI: '+ '<b>'+ Math.round(props.FUI*100)/100 + '</b>'
                            : '<b>'+ 'Click a Water Source'+'</b>');
            };
            info.addTo(map);

            var lgaDict = {};
    //                    var geojson, geojsonLabels;
            // initialise each property for of geojson
            for (j = 0; j < lgas.features.length; j++) {
                    lgas.features[j].properties.irrigated_area=0;
                    lgas.features[j].properties.irrigation_value=0;
                    lgas.features[j].properties.employment_irrigation=0;
                    lgas.features[j].properties.total_entitlement=0;
                    lgas.features[j].properties.mean_flow=0;
                    lgas.features[j].properties.DSI=0;
                    lgas.features[j].properties.FUI=0;
                    lgaDict[lgas.features[j].properties.WATER_SOUR] = lgas.features[j];
            }

            // Create parallel Coordinate
            parcoords = d3.parcoords()("#parrallel_coordinate")
                    .alpha(1)
                    .mode("queue") // progressive rendering
                    .height(pageHeight)
                    .width(document.getElementById("parrallel_coordinate").clientWidth - 10)
                    .margin({
                            top: 25,
                            left: 1,
                            right: 1,
                            bottom: 15
                    })
                    .color(function (d) { return getColorScalar(d.DSI) });


            //Read data for parallel coordinate
            d3.csv("../../pc.csv/dsi_macquaire.csv", function (data) {
                    _.each(data, function (d, i) {
                            d.index = d.index || i; //unique id
                            var water_source_name = d["Water Source"];
                            lgaDict[water_source_name].properties.irrigated_area=d["Irrigated Area"];
                            lgaDict[water_source_name].properties.irrigation_value=d["Irrigation Value ($M)"];
                            lgaDict[water_source_name].properties.employment_irrigation=d["Employment (Irrigation)"];
                            lgaDict[water_source_name].properties.total_entitlement=d["Total Entitlement"];
                            lgaDict[water_source_name].properties.mean_flow=d["Mean Flow"];
                            lgaDict[water_source_name].properties.DSI=d["DSI"];
                            lgaDict[water_source_name].properties.FUI=d["FUI"];
                            lga.push(water_source_name);
                    });

                    // add lga layer
                    geojson = L.geoJson(lgas, {
                            style: style,                            
                            onEachFeature: onEachFeature
                    }).addTo(map);

                    // add label layer
                    geojsonLabels = L.geoJson(lgaCentroids, {
                            pointToLayer: function (feature, latlng) {
                                    return  L.marker(latlng, {
                                            clickable : false,
                                            draggable : false,
                                            icon: L.divIcon({
                                            className: 'my-leaflet-div-icon',
                                            })
                                    });
                            },
                    }).addTo(map);

                    // add legend
                    legend = L.control({position: 'bottomright'});
                    legend.onAdd = function (map) {
                            var div = L.DomUtil.create('div', 'info legend'),
                            labels = [],
                            from, to;
                            labels.push(
                                            '<i style="background:' + myCols[0] + '"></i> ' + '[0, 0.3]');
//                                            1 +' (' +'1&ndash;' + Math.floor(max_row/3) + ')');
                            labels.push(
                                            '<i style="background:' + myCols[1] + '"></i> ' + '(0.3, 0.55]');
//                                            2 +' (' + (Math.floor(max_row/3)+1) + '&ndash;' + Math.ceil(2*max_row/3) + ')');
                            labels.push(
                                            '<i style="background:' + myCols[2] + '"></i> ' + '(0.55, 1]');
//                                            3 +' (' + (Math.ceil(2*max_row/3)+1) + '&ndash;' + max_row + ')');
                            div.innerHTML = '<h4>DSI</h4>' + labels.join('<br>');
                            return div;
                    };
                    legend.addTo(map);

                    //Bind data to parallel coordinate
                    parcoords.data(data)
                                    .hideAxis(["Water Source","index"])
                                    .render()
                                    .reorderable()
                                    .brushMode("1D-axes")
                                    .rate(400);

                    // setting up grid
                    var column_keys = d3.keys(data[0]);
                    var columns = column_keys.map(function(key,i) {
                            return {
                                    id: key,
                                    name: key,
                                    field: key,
                                    sortable: true}
                    });

                    var options = {
                            enableCellNavigation: true,
                            enableColumnReorder: false,
                            multiColumnSort: false,
                    };

                    var dataView = new Slick.Data.DataView();
                    var grid = new Slick.Grid("#grid", dataView, columns, options);

                    grid.autosizeColumns();

                    // wire up model events to drive the grid
                    dataView.onRowCountChanged.subscribe(function (e, args) {
                            grid.updateRowCount();
                            grid.render();
                    });

                    dataView.onRowsChanged.subscribe(function (e, args) {
                            grid.invalidateRows(args.rows);
                            grid.render();
                    });

                    // column sorting
                    var sortcol = column_keys[0];
                    var sortdir = 1;

                    function comparer(a, b) {
                            var x = a[sortcol], y = b[sortcol];
                            return (x == y ? 0 : (x > y ? 1 : -1));
                    }

                    // click header to sort grid column
                    grid.onSort.subscribe(function (e, args) {
                            sortdir = args.sortAsc ? 1 : -1;
                            sortcol = args.sortCol.field;

                            if ($.browser.msie && $.browser.version <= 8) {
                                    dataView.fastSort(sortcol, args.sortAsc);
                            } else {
                                    dataView.sort(comparer, args.sortAsc);
                            }
                    });

                    // highlight row in chart
                    grid.onMouseEnter.subscribe(function(e,args) {
                            var i = grid.getCellFromEvent(e).row;
                            var d = parcoords.brushed() || data;
                            parcoords.highlight([d[i]]);
                    });

                    grid.onMouseLeave.subscribe(function(e,args) {
                            parcoords.unhighlight();
                    });

                    // fill grid with data
                    gridUpdate(data);

                    // update grid on brush
                    parcoords.on("brush", function (d) {
                            filtered = d;
                            isSelected = true;
                            gridUpdate(d);
                            //update map
                            lgas.features.map(function (d) {d.properties.DSI = -1; });
                            geojsonLabels.getLayers().map(function (d) { d._icon.innerHTML = ""; })
                            _.each(d, function (k, i) {
                                    lgaDict[k["Water Source"]].properties.DSI = k.DSI;
                            });

                            map.removeControl(legend);
                            legend.addTo(map);
                            refreshMap(lga);
                    });


                    function gridUpdate(data) {
                            dataView.beginUpdate();
                            dataView.setItems(data);
                            dataView.endUpdate();
                    };

                    function refreshMap(updatedLGA) {
                            // go through updateLGA, or edit the values directly in the geojson layers
                            geojson.getLayers().map(function (d) {
                                    geojson.resetStyle(d);
                                    geojsonLabels.getLayers().forEach(function (z) {
                                            if (z.feature.properties.name == d.feature.properties.WATER_SOUR) {
                                                    if (d.feature.properties.DSI >= 0) {
                                                            z._icon.innerHTML=Math.round(d.feature.properties.DSI*100)/100;
                                                    } else {
                                                            z._icon.innerHTML = "";
                                                    }
                                            }
                                    });
                            })
                    }
            });
            setTimeout(function(){ map.invalidateSize()}, 1000);
        </script>
    </body>
</html>


