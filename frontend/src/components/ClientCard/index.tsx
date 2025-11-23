import { useCallback, useEffect, useRef, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { FormProvider, useForm, useWatch } from "react-hook-form";
import { ArrowLeft, Save } from "lucide-react";
import { notify } from "@/components/ui/toast";

import { useApp } from "@/context/AppContext";
import { apiRequest } from "@/config/api";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs";
import Loading from "@/components/shared/Loading";

import { GeneralTab, SaveContext } from "./General";
import { PretrialTab } from "./Pretrial";
import { JudicialTab } from "./JudicialTab";
import { FormValues, PrimaryInfoFields } from "./types";
import { convertApiDataToFormValues, createDefaultFormValues } from "./helpers";
import { StatusBadge } from "@shared/StatusBadge";
import { useModalStore } from "../Modals/ModalProvider";

function ClientCard() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { referenceData } = useApp();
  const [contract, setContract] = useState<{
    id: number;
    contractNumber: string;
    status: string;
  } | null>(null);
  const [contractData, setContractData] = useState<Record<
    string,
    unknown
  > | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const { openModal } = useModalStore();
  const form = useForm<FormValues>({
    mode: "onChange",
    defaultValues: createDefaultFormValues(),
  });
  const savedSnapshotRef = useRef<string>(
    JSON.stringify(createDefaultFormValues())
  );
  const watchedValues =
    useWatch<FormValues>({
      control: form.control,
    }) ?? form.getValues();
  // Функция для ручного сохранения
  const saveContract = useCallback(async () => {
    if (!contract || loading) return;

    const currentValues = form.getValues();
    const serialized = JSON.stringify(currentValues);

    if (serialized === savedSnapshotRef.current) {
      notify({
        message: "Нет изменений для сохранения",
        type: "info",
        duration: 3000,
      });
      return; // Нет изменений
    }

    const apiData: Record<string, unknown> = {
      basic_info: currentValues.primaryInfo || {},
      pre_court: {
        ...(currentValues.pretrial || {}),
        creditors: currentValues.pretrial?.creditors ?? [],
      },
      procedure_initiation: currentValues.introduction || {},
      procedure: currentValues.procedure || {},
    };

    try {
      await apiRequest(`/contracts/${contract.id}`, {
        method: "PUT",
        body: JSON.stringify(apiData),
      });
      savedSnapshotRef.current = serialized;
      // Сбрасываем состояние формы, чтобы isDirty стал false
      form.reset(currentValues, { keepValues: true });
      notify({ message: "Изменения успешно сохранены", type: "success" });
    } catch (err) {
      console.error("Ошибка при сохранении:", err);
      notify({
        message:
          err instanceof Error ? err.message : "Не удалось сохранить изменения",
        type: "error",
        duration: 5000,
      });
    }
  }, [contract, loading, form]);

  // Загрузка контракта из API
  useEffect(() => {
    const loadContract = async () => {
      if (!id || id === "new") {
        setLoading(false);
        setContract(null);
        setContractData(null);
        const defaults = createDefaultFormValues();
        form.reset(defaults);
        savedSnapshotRef.current = JSON.stringify(defaults);
        return;
      }

      try {
        setLoading(true);
        setError(null);
        const data = await apiRequest(`/contracts/${id}`);

        if (data && typeof data === "object") {
          // Преобразуем данные из API формата
          const formValues = convertApiDataToFormValues(data);
          form.reset(formValues);
          savedSnapshotRef.current = JSON.stringify(formValues);

          // Сохраняем базовую информацию о контракте
          setContract({
            id: Number(id),
            contractNumber:
              (data.basic_info as any)?.contractNumber || `ДГ-${id}`,
            status: (data.basic_info as any)?.status || "active",
          });
          setContractData(data);
        } else {
          setError("Контракт не найден");
        }
      } catch (err) {
        console.error("Ошибка при загрузке контракта:", err);
        setError("Не удалось загрузить контракт");
      } finally {
        setLoading(false);
      }
    };

    loadContract();
  }, [id, form]);

  // Автосохранение отключено - сохранение только по кнопке

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <Loading text="Загрузка контракта..." />
      </div>
    );
  }

  if (error || !contract) {
    return (
      <div className="flex h-full items-center justify-center">
        <Card className="p-8 text-center">
          <p className="mb-4 text-lg">{error || "Договор не найден"}</p>
          <Button onClick={() => navigate("/contracts")}>
            <ArrowLeft className="mr-2 h-4 w-4" />
            Вернуться к списку
          </Button>
        </Card>
      </div>
    );
  }

  const openDocument = async (doc: {
    id: number;
    name: string;
  }): Promise<void> => {
    if (!contract) {
      return;
    }
    openModal("previewDocumentModal", {
      document: {
        documentId: doc.id,
        contractId: contract.id,
        documentName: doc.name,
      },
    });
  };

  const onDownload = async (doc: {
    id: number;
    name: string;
  }): Promise<void> => {
    if (!contract) {
      return;
    }
    try {
      const blob = await apiRequest(`/document-templates/${doc?.id}/generate`, {
        method: "POST",
        body: JSON.stringify({ contractId: contract.id }),
        responseType: "blob",
      });
      const downloadUrl = window.URL.createObjectURL(blob);
      const link = window.document.createElement("a");

      link.href = downloadUrl;
      link.download = `${doc?.name}.docx`;
      window.document.body.appendChild(link);
      link.click();
      window.document.body.removeChild(link);
      window.URL.revokeObjectURL(downloadUrl);
    } catch (error) {
      notify({
        message:
          error instanceof Error
            ? error.message
            : "Ошибка при генерации документа",
        type: "error",
        duration: 5000,
      });
    }
  };

  const primaryInfo = (watchedValues.primaryInfo ??
    {}) as Partial<PrimaryInfoFields>;
  const fullName =
    [primaryInfo.lastName, primaryInfo.firstName, primaryInfo.middleName]
      .filter(Boolean)
      .join(" ") ||
    contract?.contractNumber ||
    "Новый договор";

  const isDirty = form.formState.isDirty;

  return (
    <SaveContext.Provider value={saveContract}>
      <FormProvider {...form}>
        <div className="space-y-6 p-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Button
                className="text-center p-2"
                variant="outline"
                onClick={() => navigate("/contracts")}
              >
                <ArrowLeft className="size-4" />
              </Button>
              <div>
                <h1 className="text-2xl font-bold">
                  Договор № {contract.contractNumber}
                </h1>
                <p className="text-sm text-muted-foreground">{fullName}</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <StatusBadge status={contract.status} />
            </div>
          </div>

          <Tabs defaultValue="primary">
            <div className="flex flex-col">
              <TabsList className="grid w-full grid-cols-3 h-11 rounded-b-none border-b-0">
                <TabsTrigger value="primary" className="text-sm font-medium">
                  Основная информация
                </TabsTrigger>
                <TabsTrigger value="pretrial" className="text-sm font-medium">
                  Досудебка
                </TabsTrigger>
                <TabsTrigger value="judicial" className="text-sm font-medium">
                  Судебка
                </TabsTrigger>
              </TabsList>
            </div>

            <div className="mt-6 pb-24">
              <GeneralTab
                contractData={contractData}
                openDocument={openDocument}
                onDownload={onDownload}
              />
              <PretrialTab
                openDocument={openDocument}
                onDownload={onDownload}
                referenceData={referenceData}
                contractData={contractData}
              />
              <JudicialTab
                openDocument={openDocument}
                onDownload={onDownload}
                referenceData={referenceData}
                contractData={contractData}
              />
            </div>
          </Tabs>

          {/* Фиксированная кнопка сохранения внизу справа */}
          <div className="fixed bottom-4 z-50" style={{ right: "2rem" }}>
            <Button
              type="button"
              onClick={saveContract}
              disabled={!isDirty || loading}
              size="lg"
              className="shadow-lg text-base px-6 py-6 bg-green-600"
            >
              <Save className="h-5 w-5 mr-2" />
              Сохранить
            </Button>
          </div>
        </div>
      </FormProvider>
    </SaveContext.Provider>
  );
}

export default ClientCard;
