declare global {
    interface Window {
        jspdf: {
            jsPDF: any;
        };
        XLSX: {
            utils: {
                json_to_sheet: (data: any) => any;
                book_new: () => any;
                book_append_sheet: (workbook: any, worksheet: any, name: string) => void;
            };
            writeFile: (workbook: any, filename: string) => void;
        };
    }
}

export {};