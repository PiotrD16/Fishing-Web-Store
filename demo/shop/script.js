 <script>
        // Funkcja do rozwijania/zwińania podkategorii
        function toggleSubcategory(categoryId) {
            var subcategory = document.getElementById(categoryId);

            if (subcategory.style.display === "block") {
                subcategory.style.display = "none";
            } else {
                subcategory.style.display = "block";
            }
        }
    </script>