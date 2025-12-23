import { useCallback, useEffect, useRef, useState } from "react";
import { useLocation, useNavigate, useParams, useSearchParams } from "react-router-dom";
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
  const location = useLocation();
  const [searchParams, setSearchParams] = useSearchParams();
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
  const { openModal, closeModal } = useModalStore();
  const form = useForm<FormValues>({
    mode: "onChange",
    defaultValues: createDefaultFormValues(),
  });
  const savedSnapshotRef = useRef<string>(
    JSON.stringify(createDefaultFormValues())
  );
  const isInitializedRef = useRef<boolean>(false);
  
  const normalizeForComparison = useCallback((values: FormValues): string => {
    const normalized = JSON.parse(JSON.stringify(values));

    if (!normalized.judicial_procedure) {
      normalized.judicial_procedure = {};
    }
    if (!normalized.judicial_procedure.creditorsClaims) {
      normalized.judicial_procedure.creditorsClaims = [];
    }

    if (!normalized.pre_court) {
      normalized.pre_court = {};
    }
    if (!Array.isArray(normalized.pre_court.creditors)) {
      normalized.pre_court.creditors = [];
    }

    return JSON.stringify(normalized);
  }, []);
  
  const watchedValues =
    useWatch<FormValues>({
      control: form.control,
    }) ?? form.getValues();
  
  const currentSerialized = normalizeForComparison(watchedValues);
  const isDirty = isInitializedRef.current && currentSerialized !== savedSnapshotRef.current;
  const currentPathRef = useRef(`${location.pathname}${location.search}`);
  const pendingPathRef = useRef<string | null>(null);
  // Функция для ручного сохранения
  const saveContract = useCallback(async () => {
    if (!contract || loading) return;

    const currentValues = form.getValues();
    const serialized = normalizeForComparison(currentValues);

    if (serialized === savedSnapshotRef.current) {
      notify({
        message: "Нет изменений для сохранения",
        type: "info",
        duration: 3000,
      });
      return; // Нет изменений
    }

    const apiData: Record<string, unknown> = {
      basic_info: {
        ...(currentValues.basic_info || {}),
        manager: currentValues.basic_info?.manager || null,
      },
      pre_court: {
        ...(currentValues.pre_court || {}),
        creditors: currentValues.pre_court?.creditors ?? [],
      },
      judicial_procedure_initiation: currentValues.judicial_procedure_initiation || {},
      judicial_procedure: {
        ...(currentValues.judicial_procedure || {}),
        creditorsClaims: (currentValues.judicial_procedure?.creditorsClaims ?? []).map((claim) => ({
          id: claim.id,
          creditorId: claim.creditorId,
          debtAmount: claim.debtAmount,
          principalAmount: claim.principalAmount,
          interest: claim.interest,
          penalty: claim.penalty,
          lateFee: claim.lateFee,
          forfeiture: claim.forfeiture,
          stateDuty: claim.stateDuty,
          basis: claim.basis,
          inclusion: claim.inclusion,
          isCreditCard: claim.isCreditCard,
          creditCardDate: claim.creditCardDate,
          judicialActDate: claim.judicialActDate,
        })),
      },
    };

    try {
      await apiRequest(`/contracts/${contract.id}`, {
        method: "PUT",
        body: JSON.stringify(apiData),
      });
      savedSnapshotRef.current = normalizeForComparison(currentValues);
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
  }, [contract, loading, form, normalizeForComparison]);

  // Загрузка контракта из API
  useEffect(() => {
    const loadContract = async () => {
      if (!id || id === "new") {
        setLoading(false);
        setContract(null);
        setContractData(null);
        const defaults = createDefaultFormValues();
        form.reset(defaults);
        savedSnapshotRef.current = normalizeForComparison(defaults);
        isInitializedRef.current = false;
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
          savedSnapshotRef.current = normalizeForComparison(formValues);
          isInitializedRef.current = false;

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
  }, [id, form, normalizeForComparison]);

  useEffect(() => {
    if (!loading && (id === "new" || contract)) {
      const timeoutId = setTimeout(() => {
        const currentValues = form.getValues();
        const serialized = normalizeForComparison(currentValues);

        savedSnapshotRef.current = serialized;
        form.reset(currentValues, { keepValues: true });
        isInitializedRef.current = true;
      }, 200);

      return () => clearTimeout(timeoutId);
    }
  }, [loading, id, contract, form, normalizeForComparison]);

  useEffect(() => {
    currentPathRef.current = `${location.pathname}${location.search}`;
  }, [location.pathname, location.search]);

  useEffect(() => {
    if (!isDirty) return;

    const openUnsavedModal = (nextPath: string) => {
      pendingPathRef.current = nextPath;
      openModal("unsavedChanges", {
        onSaveAndExit: async () => {
          await saveContract();
          closeModal("unsavedChanges");
          const target = pendingPathRef.current;
          pendingPathRef.current = null;
          if (target) {
            navigate(target);
          }
        },
        onConfirm: () => {
          closeModal("unsavedChanges");
          const target = pendingPathRef.current;
          pendingPathRef.current = null;
          if (target) {
            navigate(target);
          }
        },
        onClose: () => {
          closeModal("unsavedChanges");
          pendingPathRef.current = null;
        },
      });
    };

    const handleLinkClick = (event: MouseEvent) => {
      const anchor = (event.target as HTMLElement | null)?.closest("a[href]") as HTMLAnchorElement | null;
      if (!anchor) return;
      if (anchor.target && anchor.target !== "_self") return;
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

      const href = anchor.getAttribute("href");
      if (!href) return;

      const nextUrl = new URL(href, window.location.href);
      const currentUrl = new URL(window.location.href);
      if (nextUrl.origin !== currentUrl.origin) return;

      const nextPath = `${nextUrl.pathname}${nextUrl.search}`;
      const currentPath = currentPathRef.current;
      if (nextPath === currentPath) return;

      event.preventDefault();
      event.stopPropagation();
      openUnsavedModal(nextPath);
    };

    const handlePopState = () => {
      const nextPath = `${window.location.pathname}${window.location.search}`;
      const currentPath = currentPathRef.current;
      if (nextPath === currentPath) return;

      window.history.pushState(null, "", currentPath);
      openUnsavedModal(nextPath);
    };

    document.addEventListener("click", handleLinkClick, true);
    window.addEventListener("popstate", handlePopState);

    return () => {
      document.removeEventListener("click", handleLinkClick, true);
      window.removeEventListener("popstate", handlePopState);
    };
  }, [isDirty, openModal, closeModal, navigate]);

  useEffect(() => {
    const handleBeforeUnload = (event: BeforeUnloadEvent) => {
      if (!isDirty) return;
      event.preventDefault();
      event.returnValue = "У вас есть несохраненные изменения. Выйти без сохранения?";
    };

    window.addEventListener("beforeunload", handleBeforeUnload);
    return () => {
      window.removeEventListener("beforeunload", handleBeforeUnload);
    };
  }, [isDirty]);
  

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
          <Button onClick={() => confirmAndNavigateAway("/contracts")}>
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
    // Для шаблона с ID=200 отключаем предпросмотр, только скачивание
    if (doc.id === 200) {
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
      // Для шаблона с ID=200 используем расширение .xlsx
      link.download = `${doc?.name}.${doc.id === 200 ? 'xlsx' : 'docx'}`;
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

  const basic_info = (watchedValues.basic_info ??
    {}) as Partial<PrimaryInfoFields>;
  const fullName =
    [basic_info.lastName, basic_info.firstName, basic_info.middleName]
      .filter(Boolean)
      .join(" ") ||
    contract?.contractNumber ||
    "Новый договор";

  // Получаем текущий таб из URL или используем значение по умолчанию
  const currentTab = searchParams.get("tab") || "basic_info";
  const validTabs = ["basic_info", "pre_court", "judicial", "judicial_procedure_initiation", "judicial_procedure", "judicial_report"];
  
  // Мапим judicial_procedure_* на общий таб judicial для UI
  let activeTab = currentTab;
  if (currentTab === "judicial_procedure_initiation" || 
      currentTab === "judicial_procedure" || 
      currentTab === "judicial_report" ||
      currentTab === "judicial") {
    activeTab = "judicial";
  } else if (!validTabs.includes(currentTab)) {
    activeTab = "basic_info";
  }

  // Обработчик изменения таба
  const handleTabChange = (value: string) => {
    const newParams = new URLSearchParams();
    
    // Если кликнули на judicial, устанавливаем дефолтный подтаб
    if (value === "judicial") {
      newParams.set("tab", "judicial_procedure_initiation");
    } else {
      newParams.set("tab", value);
    }
    
    setSearchParams(newParams, { replace: true });
  };

  const confirmAndNavigateAway = (path: string) => {
    if (!isDirty) {
      navigate(path);
      return;
    }

    openModal("unsavedChanges", {
      onSaveAndExit: async () => {
        await saveContract();
        closeModal("unsavedChanges");
        navigate(path);
      },
      onConfirm: () => {
        closeModal("unsavedChanges");
        navigate(path);
      },
      onClose: () => {
        closeModal("unsavedChanges");
      },
    });
  };


  const navigateToField = (fieldInfo: { tab: string; accordion?: string; fieldId: string }) => {
    handleTabChange(fieldInfo.tab);

    const labelElement = document.querySelector(`[for="${fieldInfo.fieldId}"]`) as HTMLElement;

    if (labelElement) {
      labelElement.scrollIntoView({behavior: "smooth", block: "center"});

      setTimeout(() => {
        labelElement.click();
      }, 50)
    }
  };

  return (
    <SaveContext.Provider value={saveContract}>
      <FormProvider {...form}>
        <div className="space-y-6 p-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Button
                className="text-center p-2"
                variant="outline"
                onClick={() => confirmAndNavigateAway("/contracts")}
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

          <Tabs value={activeTab} onValueChange={handleTabChange}>
            <div className="flex flex-col">
              <TabsList className="grid w-full grid-cols-3 h-12 rounded-b-none p-0 gap-0.5">
                <TabsTrigger 
                  value="basic_info"
                  className="text-sm font-semibold rounded-lg mx-0.5 data-[state=active]:bg-blue-100/80 dark:data-[state=active]:bg-blue-900/40 data-[state=active]:text-blue-700 dark:data-[state=active]:text-blue-300 data-[state=active]:shadow-lg data-[state=active]:font-bold data-[state=inactive]:bg-muted/80 dark:data-[state=inactive]:bg-muted/70 data-[state=inactive]:text-muted-foreground/90 dark:data-[state=inactive]:text-muted-foreground/80"
                >
                  Основная информация
                </TabsTrigger>
                <TabsTrigger 
                  value="pre_court"
                  className="text-sm font-semibold rounded-lg mx-0.5 data-[state=active]:bg-blue-100/80 dark:data-[state=active]:bg-blue-900/40 data-[state=active]:text-blue-700 dark:data-[state=active]:text-blue-300 data-[state=active]:shadow-lg data-[state=active]:font-bold data-[state=inactive]:bg-muted/80 dark:data-[state=inactive]:bg-muted/70 data-[state=inactive]:text-muted-foreground/90 dark:data-[state=inactive]:text-muted-foreground/80"
                >
                  Досудебка
                </TabsTrigger>
                <TabsTrigger 
                  value="judicial"
                  className="text-sm font-semibold rounded-lg mx-0.5 data-[state=active]:bg-blue-100/80 dark:data-[state=active]:bg-blue-900/40 data-[state=active]:text-blue-700 dark:data-[state=active]:text-blue-300 data-[state=active]:shadow-lg data-[state=active]:font-bold data-[state=inactive]:bg-muted/80 dark:data-[state=inactive]:bg-muted/70 data-[state=inactive]:text-muted-foreground/90 dark:data-[state=inactive]:text-muted-foreground/80"
                >
                  Судебка
                </TabsTrigger>
              </TabsList>
            </div>

            <div className="pb-24 mt-0">
              <GeneralTab
                contractData={contractData}
                openDocument={openDocument}
                onDownload={onDownload}
                onNavigateToField={navigateToField}
                referenceData={referenceData}
              />
              <PretrialTab
                openDocument={openDocument}
                onDownload={onDownload}
                referenceData={referenceData}
                contractData={contractData}
                onNavigateToField={navigateToField}
              />
              <JudicialTab
                openDocument={openDocument}
                onDownload={onDownload}
                referenceData={referenceData}
                contractData={contractData}
                onNavigateToField={navigateToField}
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
              className="shadow-2xl text-base px-8 py-8 bg-green-600"
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
