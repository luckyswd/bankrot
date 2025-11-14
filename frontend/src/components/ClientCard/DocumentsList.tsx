import {FileText} from "lucide-react"
import {Button} from "@/components/ui/button"
import {Separator} from "@/components/ui/separator"

interface Document {
    id: number
    name: string
}

interface DocumentsListProps {
    documents: Document[]
    title: string
    onDocumentClick: (document: Document) => void
}

export const DocumentsList = ({documents, title, onDocumentClick}: DocumentsListProps): JSX.Element | null => {
    if (!documents || documents.length === 0) {
        return null
    }

    return (
        <>
            <Separator className="my-6"/>
            <div className="space-y-3">
                <h4 className="font-semibold">{title}</h4>
                {documents.map((document) => (
                    <Button
                        key={document.id}
                        onClick={() => onDocumentClick(document)}
                        variant="outline"
                        className="w-full justify-start"
                    >
                        <FileText className="mr-2 h-4 w-4"/>
                        {document.name}
                    </Button>
                ))}
            </div>
        </>
    )
}
