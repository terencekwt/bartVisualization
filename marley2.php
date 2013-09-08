<? include('../../header.php') ?>

<section class="group5">
    <h1>Departure Visualization (Marley's Train)</h1>
    <div id="frame">
</section>

</div>

<style>
   
svg {
  font: 10px sans-serif;
}

.axis path {
  display: none;
}

.axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

.station line {
  stroke: #ddd;
  stroke-dasharray: 1,1;
  shape-rendering: crispEdges;
}

.station text {
  text-anchor: end;
}

.train line {
  stroke-width: 1.5px;
}

.train circle {
  fill: #777;
  stroke: #fff;
  stroke-width: 1.5px;
}

</style>
<script src="http://d3js.org/d3.v3.min.js"></script>
<script>

var formatTime = d3.time.format("%I:%M%p");

var margin = {top: 20, right: 30, bottom: 20, left: 100},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

var x = d3.time.scale()
    .domain([parseTime("5:00AM"), parseTime("10:00AM")])
    .range([0, width]);

var y = d3.scale.linear()
    .range([0, height]);

var z = d3.scale.linear()
    .domain([.0001, .0003])
    .range(["purple", "orange"])
    .interpolate(d3.interpolateLab);

var xAxis = d3.svg.axis()
    .scale(x)
    .ticks(8)
    .tickFormat(formatTime);

var svg = d3.select("#frame").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
    .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

svg.append("defs").append("clipPath")
    .attr("id", "clip")
    .append("rect")
    .attr("y", -margin.top)
    .attr("width", width)
    .attr("height", height + margin.top + margin.bottom);

d3.json("bartSchedule.json",

    function(error, data) {
        
        var stations = data['stations'];
        var routes = data['routes'];
        
        y.domain(d3.extent(stations, 
            function(d) { 
                return d.distance; 
            }
            )
        );

        var station = svg.append("g")
            .attr("class", "station")
            .selectAll("g")
            .data(stations)
            .enter().append("g")
            .attr("transform", function(d) { return "translate(0," + y(d.distance) + ")"; });

        station.append("text")
            .attr("x", -6)
            .attr("dy", ".35em")
            .text(function(d) { return d.name; });

        station.append("line")
            .attr("x2", width);
        
        svg.append("g")
            .attr("class", "x top axis")
            .call(xAxis.orient("top"));

        svg.append("g")
            .attr("class", "x bottom axis")
            .attr("transform", "translate(0," + height + ")")
            .call(xAxis.orient("bottom"));
        
        var train = svg.append("g")
            .attr("class", "train")
            //.attr("clip-path", "url(#clip)")
            .selectAll("g")
            .data(routes)
            .enter().append("g");
            
        for(var i = 0; i < routes.length; i++){
            route = routes[i];
            //alert(route.time1 + ' ' + route.time2 + ' ' + route.y1 + ' ' + route.y2);
            svg.append("line")
            .attr("x1", x(parseTime(route.time1)))
            .attr("x2", x(parseTime(route.time2)))
            .attr("y1", y(route.y1))
            .attr("y2", y(route.y2))
            .style("stroke", "rgb(6,120,155)");
        }
        /*
        svg.append("g")
        .attr("class", "segment")
            .selectAll("g")
            .data(routes)
            .enter().append("line")
            .attr("x1", function(d) { alert("hello"); return x(parseTime(d.time1));})
            .attr("x2", function(d) { alert("hello"); return x(parseTime(d.time2));})
            .attr("y1", function(d) { alert("hello"); return y(d.y1); })
            .attr("y2", function(d) { return y(d.y2) })
            .style("stroke", "rgb(6,120,155)");
            //.style("stroke", function(d) { return z(Math.abs((d[1].station.distance - d[0].station.distance) / (d[1].time - d[0].time))); });
        
        /*
        train.selectAll("circle")
            .data(function(d) { return d.stops; })
            .enter().append("circle")
            .attr("transform", function(d) { return "translate(" + x(d.time) + "," + y(d.station.distance) + ")"; })
            .attr("r", 2);
        */
        
    }
        
);

function type(d, i) {

  // Extract the stations from the "stop|*" columns.
  if (!i) for (var k in d) {
    if (/^stop\|/.test(k)) {
      var p = k.split("|");
      stations.push({
        key: k,
        name: p[1],
        distance: +p[2],
        zone: +p[3]
      });
    }
  }

  return {
    number: d.number,
    type: d.type,
    direction: d.direction,
    stops: stations
        .map(function(s) { return {station: s, time: parseTime(d[s.key])}; })
        .filter(function(s) { return s.time != null; })
  };
}

function parseTime(s) {
  var t = formatTime.parse(s);
  if (t != null && t.getHours() < 3) t.setDate(t.getDate() + 1);
  return t;
}

</script>

<? include('../../footer.php') ?>