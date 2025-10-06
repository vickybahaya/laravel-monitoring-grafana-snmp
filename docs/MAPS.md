# Router Maps Functionality

## Overview

The maps functionality allows you to visualize router locations on an interactive map in Grafana. Routers with latitude and longitude coordinates are displayed as markers, color-coded by their status.

## Setup

### 1. Run Migration

Add latitude and longitude fields to routers:

\`\`\`bash
docker-compose exec laravel-app php artisan migrate
\`\`\`

This adds two new fields to the `routers` table:
- `latitude` (decimal 10,8) - Range: -90 to 90
- `longitude` (decimal 11,8) - Range: -180 to 180

### 2. Add Coordinates to Routers

#### Via API

**Create router with coordinates**:
\`\`\`bash
curl -X POST http://localhost:8000/api/routers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Router 1",
    "ip_address": "192.168.1.1",
    "port": 8728,
    "username": "admin",
    "password": "password",
    "category_id": 1,
    "location": "New York Office",
    "latitude": 40.7128,
    "longitude": -74.0060,
    "is_active": true
  }'
\`\`\`

**Update existing router**:
\`\`\`bash
curl -X PUT http://localhost:8000/api/routers/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": 40.7128,
    "longitude": -74.0060
  }'
\`\`\`

#### Via Database

\`\`\`sql
UPDATE routers 
SET latitude = 40.7128, longitude = -74.0060 
WHERE id = 1;
\`\`\`

### 3. Access Map Dashboard

Open Grafana and navigate to:
\`\`\`
http://localhost:3000/d/router-map
\`\`\`

Or search for "Router Map View" in dashboards.

## API Endpoints

### Get Routers with Coordinates

**Endpoint**: `GET /api/map/routers`

**Response**:
\`\`\`json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Router 1",
      "ip_address": "192.168.1.1",
      "location": "New York Office",
      "latitude": 40.7128,
      "longitude": -74.0060,
      "status": "up",
      "category": "Core",
      "last_checked_at": "2024-01-05T10:30:00Z"
    }
  ]
}
\`\`\`

### Get GeoJSON Format

**Endpoint**: `GET /api/map/geojson`

Returns router locations in GeoJSON format, compatible with mapping libraries.

**Response**:
\`\`\`json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": {
        "type": "Point",
        "coordinates": [-74.0060, 40.7128]
      },
      "properties": {
        "id": 1,
        "name": "Router 1",
        "ip_address": "192.168.1.1",
        "location": "New York Office",
        "status": "up",
        "category": "Core"
      }
    }
  ]
}
\`\`\`

### Get Prometheus Metrics

**Endpoint**: `GET /api/map/metrics`

Returns location data in Prometheus format with coordinates as labels.

## Map Dashboard Features

### Interactive Map
- **Zoom**: Mouse wheel or zoom controls
- **Pan**: Click and drag
- **Markers**: Click for router details
- **Basemap**: OpenStreetMap

### Status Indicators
- **Green markers**: Router is UP
- **Red markers**: Router is DOWN
- **Marker size**: Can be configured based on metrics

### Statistics
- Total routers on map
- Online routers count
- Offline routers count

### Location Table
Below the map, a table shows all routers with:
- Router name
- IP address
- Location name
- Coordinates (latitude/longitude)
- Status
- Category

## Finding Coordinates

### Online Tools

1. **Google Maps**:
   - Right-click on location
   - Select "What's here?"
   - Coordinates appear at bottom

2. **OpenStreetMap**:
   - Navigate to location
   - Right-click → "Show address"
   - Coordinates in URL

3. **LatLong.net**:
   - https://www.latlong.net/
   - Search by address

### Example Coordinates

Major cities:
\`\`\`
New York:     40.7128, -74.0060
London:       51.5074, -0.1278
Tokyo:        35.6762, 139.6503
Sydney:       -33.8688, 151.2093
Paris:        48.8566, 2.3522
Singapore:    1.3521, 103.8198
Dubai:        25.2048, 55.2708
Toronto:      43.6532, -79.3832
\`\`\`

## Customizing the Map

### Change Default View

Edit `grafana/dashboards/router-map.json`:

\`\`\`json
"view": {
  "lat": 40.7128,    // Center latitude
  "lon": -74.0060,   // Center longitude
  "zoom": 10         // Zoom level (1-20)
}
\`\`\`

### Change Marker Style

\`\`\`json
"style": {
  "size": {
    "fixed": 10,     // Marker size
    "max": 20,
    "min": 5
  },
  "color": {
    "field": "Value",
    "fixed": "dark-green"
  }
}
\`\`\`

### Add Heatmap Layer

Add to layers array:
\`\`\`json
{
  "type": "heatmap",
  "config": {
    "weight": {
      "fixed": 1,
      "max": 1,
      "min": 0
    }
  }
}
\`\`\`

## Integration with External Maps

### Leaflet.js

\`\`\`javascript
fetch('http://localhost:8000/api/map/geojson')
  .then(response => response.json())
  .then(data => {
    L.geoJSON(data, {
      pointToLayer: function(feature, latlng) {
        const color = feature.properties.status === 'up' ? 'green' : 'red';
        return L.circleMarker(latlng, {
          radius: 8,
          fillColor: color,
          color: '#fff',
          weight: 1,
          opacity: 1,
          fillOpacity: 0.8
        });
      },
      onEachFeature: function(feature, layer) {
        layer.bindPopup(\`
          <b>\${feature.properties.name}</b><br>
          IP: \${feature.properties.ip_address}<br>
          Status: \${feature.properties.status}
        \`);
      }
    }).addTo(map);
  });
\`\`\`

### Google Maps

\`\`\`javascript
fetch('http://localhost:8000/api/map/routers')
  .then(response => response.json())
  .then(data => {
    data.data.forEach(router => {
      const marker = new google.maps.Marker({
        position: { lat: router.latitude, lng: router.longitude },
        map: map,
        title: router.name,
        icon: router.status === 'up' 
          ? 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
          : 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
      });
    });
  });
\`\`\`

## Troubleshooting

### Routers Not Showing on Map

1. **Check coordinates are set**:
   \`\`\`sql
   SELECT id, name, latitude, longitude 
   FROM routers 
   WHERE latitude IS NOT NULL;
   \`\`\`

2. **Verify API endpoint**:
   \`\`\`bash
   curl http://localhost:8000/api/map/routers
   \`\`\`

3. **Check Prometheus metrics**:
   \`\`\`bash
   curl http://localhost:8000/api/map/metrics
   \`\`\`

### Map Not Loading

1. Check Grafana geomap plugin is installed
2. Verify internet connection (for basemap tiles)
3. Check browser console for errors

### Incorrect Marker Positions

1. Verify coordinate format (latitude, longitude)
2. Check coordinate ranges:
   - Latitude: -90 to 90
   - Longitude: -180 to 180
3. Ensure coordinates are not swapped

## Best Practices

1. **Always validate coordinates** before saving
2. **Use consistent coordinate precision** (6-8 decimal places)
3. **Add location names** for better context
4. **Group routers by region** using categories
5. **Update coordinates** when routers are relocated
6. **Test map view** after adding new routers
7. **Document coordinate sources** for future reference

## Advanced Features

### Clustering

For many routers, enable marker clustering in Grafana:

\`\`\`json
"config": {
  "cluster": {
    "enabled": true,
    "radius": 50
  }
}
\`\`\`

### Custom Tooltips

Add more information to tooltips:

\`\`\`json
"tooltip": {
  "mode": "details",
  "fields": ["router_name", "ip_address", "status", "category"]
}
\`\`\`

### Route Lines

Connect routers with lines to show network topology:

\`\`\`json
{
  "type": "route",
  "config": {
    "style": {
      "color": {
        "fixed": "blue"
      },
      "width": 2
    }
  }
}
\`\`\`
