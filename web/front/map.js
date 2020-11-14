/**
 * Display clustered markers on a map
 *
 * Note that the maps clustering module https://js.api.here.com/v3/3.1/mapsjs-clustering.js
 * must be loaded to use the Clustering

 * @param {H.Map} map A HERE Map instance within the application
 * @param {Object[]} data Raw data that contains airports' coordinates
*/




// Step 5: cluster data about airports's coordinates
// airports variable was injected at the page load



function startClustering(map,getBubbleContent,data) {
    // First we need to create an array of DataPoint objects,
    // for the ClusterProvider
console.log(data);

   var dataPoints = data.map(function (item) {
      return new H.clustering.DataPoint(item.Lat, item.Long,null,item);
    });
    // Create a clustering provider with custom options for clusterizing the input
    var clusteredDataProvider = new H.clustering.Provider(dataPoints, {
      clusteringOptions: {
        // Maximum radius of the neighbourhood
        eps: 32,
        // minimum weight of points required to form a cluster
        minWeight: 2
      }
    });

    clusteredDataProvider.addEventListener('tap', onMarkerClick);

    // Create a layer tha will consume objects from our clustering provider
    var clusteringLayer = new H.map.layer.ObjectLayer(clusteredDataProvider);
  
    // To make objects from clustering provder visible,
    // we need to add our layer to the map
    map.addLayer(clusteringLayer);
  }

  

  /**
   * Boilerplate map initialization code starts below:
   */
  
  // Step 1: initialize communication with the platform
  // In your own code, replace variable window.apikey with your own apikey
  var platform = new H.service.Platform({
    apikey: window.apikey
  });
  
  var defaultLayers = platform.createDefaultLayers();
  
  // Step 2: initialize a map
  var map = new H.Map(document.getElementById('map'), defaultLayers.vector.normal.map, {
    center: new H.geo.Point(30.789, 33.790),
    zoom: 2,
    pixelRatio: window.devicePixelRatio || 1
  });
  // add a resize listener to make sure that the map occupies the whole container
  window.addEventListener('resize', () => map.getViewPort().resize());
  
  


  function onMarkerClick(e) {

    // event target is the marker itself, group is a parent event target
    // for all objects that it contains

    var position = e.target.getGeometry(),
    // Get the data associated with that marker
    data = e.target.getData(),
    // Merge default template with the data and get HTML
    bubbleContent = getBubbleContent(data.a.data),
    bubble = onMarkerClick.bubble;

  // For all markers create only one bubble, if not created yet
  if (!bubble) {
    bubble = new H.ui.InfoBubble(position, {
      content: bubbleContent
    });
    ui.addBubble(bubble);
    // Cache the bubble object
    onMarkerClick.bubble = bubble;
  } else {
    // Reuse existing bubble object
    bubble.setPosition(position);
    bubble.setContent(bubbleContent);
    bubble.open();
  }

  // Move map's center to a clicked marker
  map.setCenter(position, true);

  
  }
  // Step 3: make the map interactive
  // MapEvents enables the event system
  // Behavior implements default interactions for pan/zoom (also on mobile touch environments)
  var behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
  
  function ZoomToTheme() {
    var that = this,
      baseTheme = new nokia.maps.clustering.MarkerTheme();
    that.getClusterPresentation = function (dataPoints) {
      var cluster = baseTheme.getClusterPresentation(dataPoints);
      cluster.$boundingBox = dataPoints.getBounds();
      return cluster;
    };
    that.getNoisePresentation = function (dataPoint) {
      var noisePoint = baseTheme.getNoisePresentation(dataPoint);
      noisePoint.$text = dataPoint.text;
      return noisePoint;
    };
  }
  // Step 4: create the default UI component, for displaying bubbles
  var ui = H.ui.UI.createDefault(map, defaultLayers);
  
  function getBubbleContent(data) {
    return [
      '<div class="bubble">',
        '<img src="/back/img/'+data.Image+'" style="width: 100px;height: 50px" ><br/>',
       '<b>'+data.Name+'</b><br/>',
      '<span>'+data.Position+'</span><br/>',
      '</div>'
    ].join('');
  }
var  ecosys;
  $.getJSON("/member/api/members",function (result){
    ecosys=result.locations;
      }
  );

$( window ).on( "load", function() { startClustering(map,getBubbleContent,ecosys)})


  