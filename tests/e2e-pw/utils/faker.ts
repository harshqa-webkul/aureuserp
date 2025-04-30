export function generateCategory() {
    const category = [
        "Fashion & Apparel",
        "Electronics & Gadgets",
        "Health & Beauty",
        "Home & Furniture",
        "Grocery & Food",
        "Sports & Fitness",
        "Toys & Games",
        "Automotive & Accessories",
        "Books & Stationery",
        "Jewelry & Watches",
    ];

    return category[Math.floor(Math.random() * category.length)];
}

export function generateProductName() {
    const products = [
        "Wireless Earbuds",
        "Smartphone Case",
        "Running Shoes",
        "Gaming Laptop",
        "Organic Green Tea",
        "Fitness Tracker",
        "Leather Wallet",
        "Bluetooth Speaker",
        "Scented Candles",
        "Electric Kettle",
    ];

    return products[Math.floor(Math.random() * products.length)];
}

export function generateName() {
    const names = [
        "John Doe",
        "Jane Smith",
        "Alice Johnson",
        "Bob Brown",
        "Charlie Davis",
        "Diana Evans",
        "Ethan Wilson",
        "Fiona Martinez",
        "George Garcia",
        "Hannah Lee",
    ];

    return names[Math.floor(Math.random() * names.length)];
}

export function generateAccountNumber() {
    const length = 10;
    let accountNumber = '';
    for (let i = 0; i < length; i++) {
        accountNumber += Math.floor(Math.random() * 10);
    }
    return accountNumber;
}

export function generateEmail() {
    const domains = ["example.com", "test.com", "demo.com"];
    const randomName = Math.random().toString(36).substring(2, 7);
    return `${randomName}@${domains[Math.floor(Math.random() * domains.length)]}`;
}

export function generatePhoneNumber() {
    const areaCode = Math.floor(Math.random() * 900) + 100; // 100-999
    const centralOfficeCode = Math.floor(Math.random() * 900) + 100; // 100-999
    const lineNumber = Math.floor(Math.random() * 9000) + 1000; // 1000-9999
    return `(${areaCode}) ${centralOfficeCode}-${lineNumber}`;
}

export function generateAddress() {
    const streets = [
        "Main St",
        "Broadway",
        "1st Ave",
        "2nd St",
        "3rd Blvd",
        "4th Rd",
        "5th Ln",
        "6th Dr",
        "7th Ct",
        "8th Pl",
    ];

    const randomStreet = streets[Math.floor(Math.random() * streets.length)];
    const randomNumber = Math.floor(Math.random() * 1000) + 1; // 1-1000
    return `${randomNumber} ${randomStreet}`;
}

export function generateBankName() {
    const banks = [
        "Bank of America",
        "Chase Bank",
        "Wells Fargo",
        "Citibank",
        "PNC Bank",
        "Capital One",
        "TD Bank",
        "US Bank",
        "BB&T Bank",
        "SunTrust Bank",
    ];

    return banks[Math.floor(Math.random() * banks.length)];
}

export function generateIncoterms() {
    const incoterms = [
        "EXW (Ex Works)",
        "FCA (Free Carrier)",
        "CPT (Carriage Paid To)",
        "CIP (Carriage and Insurance Paid To)",
        "DAT (Delivered at Terminal)",
        "DAP (Delivered at Place)",
        "DDP (Delivered Duty Paid)",
        "FOB (Free on Board)",
        "CFR (Cost and Freight)",
        "CIF (Cost, Insurance, and Freight)",
    ];

    return incoterms[Math.floor(Math.random() * incoterms.length)];
}

export function generateTerm() {
    const terms = [
        "Net 30",
        "Net 60",
        "Net 90",
        "Due on Receipt",
        "End of Month (EOM)",
        "Cash in Advance (CIA)",
        "Cash on Delivery (COD)",
        "Letter of Credit (LC)",
        "Documentary Collection (D/C)",
        "Open Account",
    ];

    return terms[Math.floor(Math.random() * terms.length)];
}

export function generateTaxGroup() {
    const taxGroups = [
        "Standard Rate",
        "Reduced Rate",
        "Zero Rate",
        "Exempt",
        "Outside Scope",
        "Input Tax Credit",
        "Reverse Charge",
        "Flat Rate Scheme",
        "Margin Scheme",
        "Tour Operators Margin Scheme (TOMS)",
    ];

    return taxGroups[Math.floor(Math.random() * taxGroups.length)];
}

export function generateTaxName() {
    const taxNames = [
        "VAT",
        "GST",
        "Sales Tax",
        "Service Tax",
        "Excise Duty",
        "Customs Duty",
        "Withholding Tax",
        "Capital Gains Tax",
        "Corporate Tax",
        "Income Tax",
    ];

    return taxNames[Math.floor(Math.random() * taxNames.length)];
}

export function generateAttribute() {
    const attributes = {
        Size: ['S', 'M', 'L'],
        Color: ['Red', 'Black', 'Yellow'],
        Material: ['Cotton', 'Polyester', 'Wool'],
        Fit: ['Regular', 'Slim', 'Loose']
    };

    const attributeKeys = Object.keys(attributes);
    const randomAttribute = attributeKeys[Math.floor(Math.random() * attributeKeys.length)];
    const values = attributes[randomAttribute];

    return {
        attribute: randomAttribute,
        values: values
    };
}

