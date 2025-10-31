let map;

function initMap() {
  // Center map on Kuala Lumpur
  const center = { lat: 3.1390, lng: 101.6869 };

  // Create the map
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 13,
    center: center,
  });

  // Store the map globally so addServerMarkers can use it
  window.map = map;

  // Call the function to add server-side program markers
  if (typeof window.addServerMarkers === 'function') {
    window.addServerMarkers();
  }
}

