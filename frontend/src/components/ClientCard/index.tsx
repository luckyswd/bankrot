import { useCallback, useEffect, useRef, useState } from "react";
import { useNavigate, useParams, useSearchParams } from "react-router-dom";
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
      basic_info: currentValues.basic_info || {},
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

  const isDirty = form.formState.isDirty;

  // Получаем текущий таб из URL или используем значение по умолчанию
  const currentTab = searchParams.get("tab") || "primary";
  const validTabs = ["primary", "pre_court", "judicial"];
  const activeTab = validTabs.includes(currentTab) ? currentTab : "primary";

  // Обработчик изменения таба
  const handleTabChange = (value: string) => {
    const newParams = new URLSearchParams();
    newParams.set("tab", value);
    // Удаляем subTab если переключаемся не на judicial
    if (value !== "judicial") {
      newParams.delete("subTab");
    }
    setSearchParams(newParams, { replace: true });
  };

  // Функция навигации к полю
  const navigateToField = (fieldInfo: { tab: string; accordion?: string; fieldId: string }) => {
    // Переключаем таб
    handleTabChange(fieldInfo.tab);

    // Если есть аккордеон, открываем его и устанавливаем subTab для judicial
    if (fieldInfo.accordion) {
      if (fieldInfo.tab === "judicial") {
        const newParams = new URLSearchParams(searchParams);
        newParams.set("tab", fieldInfo.tab);
        newParams.set("subTab", fieldInfo.accordion);
        setSearchParams(newParams, { replace: true });
      }

      // Открываем аккордеон через клик на триггер
      setTimeout(() => {
        // Ищем AccordionItem по значению и находим его триггер
        const accordionItems = document.querySelectorAll('[data-radix-accordion-item]');
        let accordionTrigger: HTMLElement | null = null;
        
        accordionItems.forEach((item) => {
          const value = item.getAttribute('data-radix-accordion-item');
          if (value === fieldInfo.accordion) {
            const trigger = item.querySelector('button[data-radix-accordion-trigger]') as HTMLElement;
            if (trigger && trigger.getAttribute('aria-expanded') === 'false') {
              accordionTrigger = trigger;
            }
          }
        });

        // Если не нашли через data-атрибуты, ищем через aria-controls
        if (!accordionTrigger && fieldInfo.accordion) {
          const allTriggers = document.querySelectorAll('button[data-radix-accordion-trigger]');
          for (const trigger of Array.from(allTriggers)) {
            const controls = trigger.getAttribute('aria-controls');
            if (controls && fieldInfo.accordion && controls.includes(fieldInfo.accordion) && trigger.getAttribute('aria-expanded') === 'false') {
              accordionTrigger = trigger as HTMLElement;
              break;
            }
          }
        }

        if (accordionTrigger) {
          accordionTrigger.click();
        }

        // Устанавливаем фокус на поле
        setTimeout(() => {
          let fieldElement: HTMLElement | null = null;
          
          // Сначала пробуем найти по точному ID
          fieldElement = document.getElementById(fieldInfo.fieldId);
          
          // Если не нашли по ID, пробуем найти по name
          if (!fieldElement) {
            fieldElement = document.querySelector(`input[name="${fieldInfo.fieldId}"]`) as HTMLElement;
          }
          
          // Если все еще не нашли, пробуем найти по частичному совпадению ID
          if (!fieldElement) {
            const allInputs = document.querySelectorAll('input');
            for (const input of Array.from(allInputs)) {
              const inputId = input.getAttribute('id') || '';
              const inputName = input.getAttribute('name') || '';
              // Проверяем точное совпадение или вхождение части fieldId
              if (inputId === fieldInfo.fieldId || 
                  inputName === fieldInfo.fieldId ||
                  inputId.includes(fieldInfo.fieldId) ||
                  inputName.includes(fieldInfo.fieldId)) {
                fieldElement = input as HTMLElement;
                break;
              }
            }
          }
          
          // Если все еще не нашли, пробуем найти по последней части fieldId (имя поля)
          if (!fieldElement) {
            const fieldName = fieldInfo.fieldId.split('.').pop() || '';
            fieldElement = document.querySelector(`input[id*="${fieldName}"], input[name*="${fieldName}"]`) as HTMLElement;
          }
          
          if (fieldElement) {
            fieldElement.scrollIntoView({ behavior: "smooth", block: "center" });
            // Небольшая задержка перед фокусом для корректной прокрутки
            setTimeout(() => {
              fieldElement?.focus();
              // Подсвечиваем поле
              fieldElement?.classList.add("ring-2", "ring-primary", "ring-offset-2");
              setTimeout(() => {
                fieldElement?.classList.remove("ring-2", "ring-primary", "ring-offset-2");
              }, 2000);
            }, 100);
          }
        }, 400);
      }, 300);
    } else {
      // Если нет аккордеона, просто устанавливаем фокус на поле
      setTimeout(() => {
        let fieldElement: HTMLElement | null = null;
        
        // Сначала пробуем найти по точному ID
        fieldElement = document.getElementById(fieldInfo.fieldId);
        
        // Если не нашли по ID, пробуем найти по name
        if (!fieldElement) {
          fieldElement = document.querySelector(`input[name="${fieldInfo.fieldId}"]`) as HTMLElement;
        }
        
        // Если все еще не нашли, пробуем найти по частичному совпадению ID
        if (!fieldElement) {
          const allInputs = document.querySelectorAll('input');
          for (const input of Array.from(allInputs)) {
            const inputId = input.getAttribute('id') || '';
            const inputName = input.getAttribute('name') || '';
            // Проверяем точное совпадение или вхождение части fieldId
            if (inputId === fieldInfo.fieldId || 
                inputName === fieldInfo.fieldId ||
                inputId.includes(fieldInfo.fieldId) ||
                inputName.includes(fieldInfo.fieldId)) {
              fieldElement = input as HTMLElement;
              break;
            }
          }
        }
        
        // Если все еще не нашли, пробуем найти по последней части fieldId (имя поля)
        if (!fieldElement) {
          const fieldName = fieldInfo.fieldId.split('.').pop() || '';
          fieldElement = document.querySelector(`input[id*="${fieldName}"], input[name*="${fieldName}"]`) as HTMLElement;
        }
        
        if (fieldElement) {
          fieldElement.scrollIntoView({ behavior: "smooth", block: "center" });
          // Небольшая задержка перед фокусом для корректной прокрутки
          setTimeout(() => {
            fieldElement?.focus();
            // Подсвечиваем поле
            fieldElement?.classList.add("ring-2", "ring-primary", "ring-offset-2");
            setTimeout(() => {
              fieldElement?.classList.remove("ring-2", "ring-primary", "ring-offset-2");
            }, 2000);
          }, 100);
        }
      }, 300);
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

          <Tabs value={activeTab} onValueChange={handleTabChange}>
            <div className="flex flex-col">
              <TabsList className="grid w-full grid-cols-3 h-12 rounded-b-none p-0 gap-0.5">
                <TabsTrigger 
                  value="primary" 
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

            <div className="mt-6 pb-24">
              <GeneralTab
                contractData={contractData}
                openDocument={openDocument}
                onDownload={onDownload}
                onNavigateToField={navigateToField}
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
