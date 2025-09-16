let map;

function viewDetails(program) {
  document.getElementById("modal").style.display = "block";
  if (program === "recycling") {
    document.getElementById("modal-title").innerText = "Recycling Drive";
    document.getElementById("modal-desc").innerText = "Bring recyclables to Community Hall, 21 Sept, 10 AM.";
  }
  if (program === "energy") {
    document.getElementById("modal-title").innerText = "Energy Saving Workshop";
    document.getElementById("modal-desc").innerText = "Learn simple energy conservation tips at Green Center, 25 Sept.";
  }
}

function closeModal() {
  document.getElementById("modal").style.display = "none";
}

function initMap() {
  // Center map on Kuala Lumpur
  const center = { lat: 3.1390, lng: 101.6869 };

  // Create the map
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 13,
    center: center,
  });

  // Example marker (now stored in a variable!)
  const marker = new google.maps.Marker({
    position: center,
    map: map,
    title: "Recycling Drive",
  });

  const info = new google.maps.InfoWindow({
    content: "<b>Recycling Drive</b><br>Community Hall<br>Bring recyclables!"
  });

  marker.addListener("click", function () {
    info.open(map, marker);
  });

  // Attach search bar to Google Places
  const input = document.getElementById("searchInput");
  const searchBox = new google.maps.places.SearchBox(input);

  // Bias results to map bounds
  map.addListener("bounds_changed", function () {
    searchBox.setBounds(map.getBounds());
  });

  searchBox.addListener("places_changed", function () {
    const places = searchBox.getPlaces();
    if (places.length === 0) return;

    const bounds = new google.maps.LatLngBounds();

    places.forEach(function (place) {
      if (!place.geometry) return;

      // Drop marker at searched place
      new google.maps.Marker({
        map: map,
        title: place.name,
        position: place.geometry.location
      });

      if (place.geometry.viewport) {
        bounds.union(place.geometry.viewport);
      } else {
        bounds.extend(place.geometry.location);
      }
    });

    map.fitBounds(bounds);
  });
}
