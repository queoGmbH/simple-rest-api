/**
 * Endpoint List - Client-side version filtering
 */
document.addEventListener('DOMContentLoaded', function() {
    const versionFilter = document.getElementById('version-filter');

    if (!versionFilter) {
        return;
    }

    const endpointPanels = document.querySelectorAll('.endpoint-panel');

    // Collect all unique versions from endpoints
    const versions = new Set();
    endpointPanels.forEach(function(panel) {
        const version = panel.getAttribute('data-version');
        if (version && version !== '') {
            versions.add(version);
        }
    });

    // Sort versions (basic semantic version sort)
    const sortedVersions = Array.from(versions).sort(function(a, b) {
        const aParts = a.split('.').map(function(n) { return parseInt(n, 10) || 0; });
        const bParts = b.split('.').map(function(n) { return parseInt(n, 10) || 0; });

        // Pad arrays to same length
        while (aParts.length < 3) aParts.push(0);
        while (bParts.length < 3) bParts.push(0);

        // Compare each part
        for (let i = 0; i < 3; i++) {
            if (aParts[i] !== bParts[i]) {
                return aParts[i] - bParts[i];
            }
        }
        return 0;
    });

    // Populate dropdown with versions
    sortedVersions.forEach(function(version) {
        const option = document.createElement('option');
        option.value = version;
        option.textContent = 'v' + version;
        versionFilter.appendChild(option);
    });

    // Only show filter if there are versions
    if (sortedVersions.length === 0) {
        versionFilter.parentElement.style.display = 'none';
    }

    // Add filter event listener
    versionFilter.addEventListener('change', function() {
        const selectedVersion = this.value;

        endpointPanels.forEach(function(panel) {
            const panelVersion = panel.getAttribute('data-version');

            if (selectedVersion === '') {
                // Show all endpoints
                panel.style.display = '';
            } else if (!panelVersion || panelVersion === '') {
                // Hide unversioned endpoints when a version is selected
                panel.style.display = 'none';
            } else if (panelVersion === selectedVersion || panelVersion.startsWith(selectedVersion + '.')) {
                // Show endpoints matching the selected version (exact or semantic match)
                panel.style.display = '';
            } else {
                // Hide non-matching endpoints
                panel.style.display = 'none';
            }
        });
    });
});
