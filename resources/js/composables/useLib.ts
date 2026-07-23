export function useLib() {
    const downloadFile = (url: string, filename?: string) => {
        if (!url) return;
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        if (filename) link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    return {
        downloadFile,
    };
}
