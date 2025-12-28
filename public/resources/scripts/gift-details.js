jQuery(document).ready(function ($) {
    let maxGlobalQuantity = 4; // Maximum total quantity allowed globally
    let selectedProducts = []; // Array to store product IDs and their quantities
    let container = $(".selected-products-container"); // Parent container for dynamic divs

    function updateSelectedItems() {
        let currentGlobalQuantity = 0;
        selectedProducts = []; // Reset array
        container.empty(); // Clear previous divs before updating

        // Reset hidden input values
        for (let i = 1; i <= maxGlobalQuantity; i++) {
            $(`#productprice${i}`).val(0);
        }

        $('.qty').each(function () {
            let productId = $(this).data('id'); // Get product ID from data-id attribute
            let quantity = parseInt($(this).find('input').val(), 10) || 0;
            let productImage = $(this).data('image') || ""; // Get product image if available
            let productPrice = parseFloat($(this).data('price')) || 0; // Get product price if available

            if (quantity > 0) {
                selectedProducts.push({ pid: productId, qty: quantity });

                // Create divs dynamically based on quantity
                for (let i = 0; i < quantity; i++) {
                    let divClass = `selectedProduct${currentGlobalQuantity + 1}`;
                    let newDiv = `<div class="${divClass} selectedProduct" 
                                    data-value="${productId}" 
                                    data-image="${productImage}" 
                                    data-price="${productPrice}">
                                  </div>`;
                    container.append(newDiv);

                    // Set the price in the hidden input field
                    if (currentGlobalQuantity < maxGlobalQuantity) {
                        $(`#productprice${currentGlobalQuantity + 1}`).val(productPrice);
                    }

                    currentGlobalQuantity++;
                }
            }
        });

        console.log("Selected Products:", selectedProducts);

        // Disable buttons if the global quantity limit is reached
        if (currentGlobalQuantity >= maxGlobalQuantity) {
            $('html, body').animate({ scrollTop: 0 }, 'slow'); // Smooth scroll to top
            $('.plus').prop('disabled', true);
        } else {
            $('.plus').prop('disabled', false);
        }

        // Call calcprice() after updating selected items
        calcprice();
        
        let $listItems = $('.selected_item ul li');
        $listItems.removeClass('active'); // Remove active class from all items

        $listItems.each(function (index) {
            if (index < currentGlobalQuantity) {
                $(this).addClass('active');
            }
        });
    }

    // Function to validate and adjust input value if necessary
    function validateInput($input) {
        let currentGlobalQuantity = 0;

        // Calculate the current total quantity
        $('.qty input').each(function () {
            currentGlobalQuantity += parseInt($(this).val(), 10) || 0;
        });

        let individualQuantity = parseInt($input.val(), 10) || 0;
        let maxIndividualQuantity = parseInt($input.attr('max'), 10) || maxGlobalQuantity;

        // Adjust if the individual or global limit is exceeded
        if (currentGlobalQuantity > maxGlobalQuantity) {
            let excessQuantity = currentGlobalQuantity - maxGlobalQuantity;
            $input.val(individualQuantity - excessQuantity);
        }

        if (individualQuantity > maxIndividualQuantity) {
            $input.val(maxIndividualQuantity);
        }
    }

    // Event listeners for plus and minus buttons
    $('.qty_area').each(function () {
        let $qtyArea = $(this);
        let $input = $qtyArea.find('.qty input');
        let $plus = $qtyArea.find('.qty .plus');
        let $minus = $qtyArea.find('.qty .minus');

        $plus.on('click', function () {
            let currentValue = parseInt($input.val(), 10) || 0;
            let max = parseInt($input.attr('max'), 10) || maxGlobalQuantity;
            let currentGlobalQuantity = 0;

            // Calculate the current total quantity
            $('.qty input').each(function () {
                currentGlobalQuantity += parseInt($(this).val(), 10) || 0;
            });

            // Increment only if below max and global limit is not reached
            if (currentValue < max && currentGlobalQuantity < maxGlobalQuantity) {
                $input.val(currentValue + 1).trigger('change');
            }
            return false;
        });

        $minus.on('click', function () {
            let currentValue = parseInt($input.val(), 10) || 0;

            // Allow decrementing down to 0
            if (currentValue > 0) {
                $input.val(currentValue - 1).trigger('change');
            }
            return false;
        });

        // Validate input on manual change
        $input.on('input', function () {
            validateInput($input);
            updateSelectedItems();
        });

        // Update global quantity and active classes on input change
        $input.on('change', function () {
            validateInput($input);
            updateSelectedItems();
        });
    });

    // Initialize active states on page load
    updateSelectedItems();
});

// Function to calculate the total price
function calcprice() {
    // var currencycode = $("#currencycode").val();
    // var sellingprice = parseFloat($("#sellingprice").val().replace(',', '')) || 0;
    // var productprice1 = parseFloat($("#productprice1").val().replace(',', '')) || 0;
    // var productprice2 = parseFloat($("#productprice2").val().replace(',', '')) || 0;
    // var productprice3 = parseFloat($("#productprice3").val().replace(',', '')) || 0;
    // var productprice4 = parseFloat($("#productprice4").val().replace(',', '')) || 0;

  //  $("#price").html(currencycode + ' ' + (sellingprice + productprice1 + productprice2 + productprice3 + productprice4).toFixed(3));
}
