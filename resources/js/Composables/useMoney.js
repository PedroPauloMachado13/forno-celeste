/**
 * Money formatting helpers for Brazilian Real (BRL).
 * All backend money values are integer cents.
 */
export function useMoney() {
    /**
     * Convert cents integer to a formatted BRL string: "R$ 5,60"
     * @param {number} cents
     * @param {number} decimals - number of decimal places (default 2)
     * @returns {string}
     */
    function formatMoney(cents, decimals = 2) {
        if (cents === null || cents === undefined) return 'R$ 0,00';
        const value = cents / 100;
        const formatted = value.toFixed(decimals).replace('.', ',');
        // Add thousand separators
        const parts = formatted.split(',');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return `R$ ${parts.join(',')}`;
    }

    /**
     * Parse a Brazilian money string back to cents integer.
     * Accepts "5,60", "R$ 5,60", "1.234,56", etc.
     * @param {string} str
     * @returns {number}
     */
    function parseMoney(str) {
        if (!str) return 0;
        // Remove currency symbol, spaces, and thousand separators
        const cleaned = String(str)
            .replace(/R\$\s*/g, '')
            .replace(/\./g, '')
            .replace(',', '.');
        const value = parseFloat(cleaned);
        if (isNaN(value)) return 0;
        return Math.round(value * 100);
    }

    /**
     * Convert cents to reais float (for calculations).
     * @param {number} cents
     * @returns {number}
     */
    function centsToReais(cents) {
        if (cents === null || cents === undefined) return 0;
        return cents / 100;
    }

    return { formatMoney, parseMoney, centsToReais };
}
